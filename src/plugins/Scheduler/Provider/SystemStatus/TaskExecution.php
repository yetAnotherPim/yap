<?php

declare(strict_types=1);

namespace App\plugins\Scheduler\Provider\SystemStatus;

use App\Controller\Provider\SystemStatus\Message\Enum\MessageType;
use App\Controller\Provider\SystemStatus\Message\SystemStatusMessage;
use App\Controller\Provider\SystemStatus\SystemCheckInterface;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Translation\TranslatableMessage;

class TaskExecution implements SystemCheckInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return list<SystemStatusMessage>
     */
    public function check(): array
    {
        if ($this->wereTasksExecutedRecently()) {
            return [];
        }

        return [
            new SystemStatusMessage(
                MessageType::Warning,
                new TranslatableMessage(
                    'yap_scheduler.tasks.warning.not_active',
                    [],
                    'scheduler',
                )
            ),
        ];
    }

    private function wereTasksExecutedRecently(): bool
    {
        try {
            /** @phpstan-var string|false|null $lastRun */
            $lastRun = $this->entityManager
                ->getConnection()
                ->fetchOne('SELECT last_run FROM yap_scheduler LIMIT 1');
        } catch (Exception) {
            return false;
        }

        if (false === $lastRun || null === $lastRun) {
            return false;
        }

        return new \DateTimeImmutable($lastRun)
            >= new \DateTimeImmutable('-24 hours');
    }
}
