<?php

declare(strict_types=1);

namespace App\plugins\Scheduler\Task;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

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

    /**
     * @throws Exception
     */
    protected function logExecution(): void
    {
        $this->entityManager
            ->getConnection()
            ->executeStatement(
                <<<'SQL'
                    INSERT INTO yap_scheduler (id, last_run)
                    VALUES (1, CURRENT_TIMESTAMP)
                    ON CONFLICT (id)
                    DO UPDATE SET last_run = CURRENT_TIMESTAMP
                SQL,
            );
    }
}
