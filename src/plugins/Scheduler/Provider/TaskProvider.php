<?php

declare(strict_types=1);

namespace App\plugins\Scheduler\Provider;

use App\plugins\Scheduler\Task\AbstractTask;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class TaskProvider
{
    /**
     * @param iterable<AbstractTask> $tasks
     */
    public function __construct(
        #[AutowireIterator('yap.scheduler.task')]
        private readonly iterable $tasks,
    ) {
    }

    /**
     * @return list<AbstractTask>
     */
    public function getTasks(): array
    {
        return iterator_to_array(
            $this->tasks,
            false,
        );
    }
}
