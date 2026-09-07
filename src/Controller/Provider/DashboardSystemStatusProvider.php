<?php

declare(strict_types=1);

namespace App\Controller\Provider;

use App\Controller\Provider\SystemStatus\Message\SystemStatusMessage;
use App\Controller\Provider\SystemStatus\SystemCheckInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

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
