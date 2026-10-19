<?php

declare(strict_types=1);

namespace App\Tests\unit\plugins\Scheduler\Controller\Admin;

use App\plugins\Scheduler\Controller\Admin\TasksController;
use App\plugins\Scheduler\Provider\TaskProvider;
use App\plugins\Scheduler\Task\AbstractTask;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(TasksController::class)]
class TasksControllerTest extends MockeryTestCase
{
    private TaskProvider&MockInterface $taskProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taskProvider = \Mockery::mock(TaskProvider::class);
    }

    public function testIndexRendersTasks(): void
    {
        $task1 = \Mockery::mock(AbstractTask::class);
        $task2 = \Mockery::mock(AbstractTask::class);

        $tasks = [
            $task1,
            $task2,
        ];

        $response = new Response();

        $this->taskProvider
            ->shouldReceive('getTasks')
            ->once()
            ->andReturn($tasks);

        $controller = \Mockery::mock(
            TasksController::class,
            [$this->taskProvider],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('render')
            ->once()
            ->with(
                '@Scheduler/tasks/index.html.twig',
                [
                    'tasks' => $tasks,
                ],
            )
            ->andReturn($response);

        $result = $controller->index();

        $this->assertSame($response, $result);
    }
}
