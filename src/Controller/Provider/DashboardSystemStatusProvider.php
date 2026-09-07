<?php

declare(strict_types=1);

namespace App\Controller\Provider;

use App\Controller\Provider\SystemStatus\Message\SystemStatusMessage;
use App\Controller\Provider\SystemStatus\SystemCheckInterface;
use App\Repository\FieldTypeRepository;
use App\Repository\IdentifierTypeRepository;
use App\Repository\LocaleRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductTypeRepository;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\Translation\TranslatableMessage;

class DashboardSystemStatusProvider
{
    /**
     * @param iterable<SystemCheckInterface> $checks
     */
    public function __construct(
        #[AutowireIterator('system_status.checker')]
        private readonly iterable $checks,
    ) {
    }

    /**
     * @return list<SystemStatusMessage>
     */
    public function getMessages(): array
    {
        $messages = [];

        foreach ($this->checks as $check) {
            array_push($messages, ...$check->check());
        }

        return $messages;
    }
}
