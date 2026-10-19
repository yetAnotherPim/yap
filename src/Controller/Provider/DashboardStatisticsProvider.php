<?php

declare(strict_types=1);

namespace App\Controller\Provider;

use App\Controller\Provider\Dto\DashboardStatistic;
use App\Messenger\QueueMonitor\QueueMonitor;
use App\Repository\FieldTypeRepository;
use App\Repository\IdentifierTypeRepository;
use App\Repository\LocaleRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductTypeRepository;
use App\Repository\UserRepository;

class DashboardStatisticsProvider
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ProductTypeRepository $productTypeRepository,
        private readonly FieldTypeRepository $fieldTypeRepository,
        private readonly UserRepository $userRepository,
        private readonly LocaleRepository $localeRepository,
        private readonly IdentifierTypeRepository $identifierTypeRepository,
        private readonly QueueMonitor $queueMonitor,
    ) {
    }

    /**
     * @return array<string, int|DashboardStatistic>
     *
     * @throws \DateMalformedStringException
     */
    public function getStatistics(): array
    {
        $productUpdateCount = (int) $this->queueMonitor->getMessageCount('product_update');
        $productUpdateFailedCount = (int) $this->queueMonitor->getMessageCount('product_update_failed');

        return [
            'product.entity.title_plural' => $this->productRepository->count([]),
            'dashboard.products.latest' => $this->productRepository->countCreatedWithinLastDays(10),
            'locale.entity.title_plural' => $this->localeRepository->count([]),
            'identifier_type.entity.title_plural' => $this->identifierTypeRepository->count([]),
            'product_type.entity.title_plural' => $this->productTypeRepository->count([]),
            'field_type.entity.title_plural' => $this->fieldTypeRepository->count([]),
            'user.entity.title_plural' => $this->userRepository->count([]),
            'queue.stats.product_update' => new DashboardStatistic(
                value: $productUpdateCount,
                color: 'info',
            ),
            'queue.stats.product_update_failed' => new DashboardStatistic(
                value: $productUpdateFailedCount,
                color: $productUpdateFailedCount > 0 ? 'warning' : null,
            ),
        ];
    }
}
