<?php

declare(strict_types=1);

namespace App\plugins\Scheduler\Task;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\String\UnicodeString;

#[AutoconfigureTag('yap.scheduler.task')]
abstract class AbstractTask
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @throws Exception
     */
    public function __invoke(): void
    {
        $this->logExecution();

        $this->run();
    }

    abstract public function run(): void;

    public function getTranslationKey(): string
    {
        return new UnicodeString($this::class)
            ->afterLast('\\')
            ->snake()
            ->toString();
    }

    /**
     * @throws Exception
     */
    protected function logExecution(): void
    {
        $connection = $this->entityManager->getConnection();

        $updated = $connection->executeStatement(
            'UPDATE yap_scheduler SET last_run = CURRENT_TIMESTAMP WHERE id = 1',
        );

        if (0 === $updated) {
            try {
                $connection->executeStatement(
                    'INSERT INTO yap_scheduler (id, last_run) VALUES (1, CURRENT_TIMESTAMP)',
                );
            } catch (UniqueConstraintViolationException) {
                // The scheduler row was created concurrently.
            }
        }
    }
}
