<?php

declare(strict_types=1);

namespace App\Tests\unit\plugins\Scheduler\Provider;

use App\plugins\Scheduler\Provider\TaskProvider;
use App\plugins\Scheduler\Task\AbstractTask;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TaskProvider::class)]
class TaskProviderTest extends MockeryTestCase
{
    public function testGetTasksReturnsTasksAsList(): void
    {
        $task1 = \Mockery::mock(AbstractTask::class);
        $task2 = \Mockery::mock(AbstractTask::class);

        $tasks = new \ArrayIterator([
            'task1' => $task1,
            'task2' => $task2,
        ]);

        $taskProvider = new TaskProvider($tasks);

        $result = $taskProvider->getTasks();

        $this->assertSame(
            [
                $task1,
                $task2,
            ],
            $result,
        );
    }

    public function testGetTasksReturnsEmptyListWhenNoTasksExist(): void
    {
        $taskProvider = new TaskProvider(
            new \ArrayIterator([]),
        );

        $this->assertSame(
            [],
            $taskProvider->getTasks(),
        );
    }
}
