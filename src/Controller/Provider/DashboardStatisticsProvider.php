<?php

declare(strict_types=1);

namespace App\Controller\Provider;

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
    ) {
    }

    /**
     * @return array<string, int>
     *
     * @throws \DateMalformedStringException
     */
    public function getStatistics(): array
    {
        return [
            'product.entity.title_plural' => $this->productRepository->count([]),
            'dashboard.products.latest' => $this->productRepository->countCreatedWithinLastDays(10),
            'locale.entity.title_plural' => $this->localeRepository->count([]),
            'identifier_type.entity.title_plural' => $this->identifierTypeRepository->count([]),
            'product_type.entity.title_plural' => $this->productTypeRepository->count([]),
            'field_type.entity.title_plural' => $this->fieldTypeRepository->count([]),
            'user.entity.title_plural' => $this->userRepository->count([]),
        ];
    }
}
