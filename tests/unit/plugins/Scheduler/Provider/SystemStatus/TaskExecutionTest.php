<?php

declare(strict_types=1);

namespace App\Tests\unit\plugins\Scheduler\Provider\SystemStatus;

use App\Controller\Provider\SystemStatus\Message\Enum\MessageType;
use App\Controller\Provider\SystemStatus\Message\SystemStatusMessage;
use App\plugins\Scheduler\Provider\SystemStatus\TaskExecution;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Translation\TranslatableMessage;

#[CoversClass(TaskExecution::class)]
#[UsesClass(SystemStatusMessage::class)]
final class TaskExecutionTest extends MockeryTestCase
{
    private EntityManagerInterface&MockInterface $entityManager;
    private Connection&MockInterface $connection;

    private TaskExecution $taskExecution;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->connection = \Mockery::mock(Connection::class);

        $this->entityManager
            ->shouldReceive('getConnection')
            ->once()
            ->andReturn($this->connection);

        $this->taskExecution = new TaskExecution(
            $this->entityManager,
        );
    }

    public function testCheckReturnsWarningWhenTaskWasNeverExecuted(): void
    {
        $this->connection
            ->shouldReceive('fetchOne')
            ->once()
            ->with('SELECT last_run FROM yap_scheduler LIMIT 1')
            ->andReturn(false);

        $messages = $this->taskExecution->check();

        $this->assertEquals(
            [
                new SystemStatusMessage(
                    MessageType::Warning,
                    new TranslatableMessage(
                        'yap_scheduler.tasks.warning.not_active',
                        [],
                        'scheduler',
                    ),
                ),
            ],
            $messages,
        );
    }

    public function testCheckReturnsWarningWhenLastExecutionIsOlderThan24Hours(): void
    {
        $this->connection
            ->shouldReceive('fetchOne')
            ->once()
            ->with('SELECT last_run FROM yap_scheduler LIMIT 1')
            ->andReturn(
                (new \DateTimeImmutable('-25 hours'))
                    ->format('Y-m-d H:i:s'),
            );

        $messages = $this->taskExecution->check();

        $this->assertEquals(
            [
                new SystemStatusMessage(
                    MessageType::Warning,
                    new TranslatableMessage(
                        'yap_scheduler.tasks.warning.not_active',
                        [],
                        'scheduler',
                    ),
                ),
            ],
            $messages,
        );
    }

    public function testCheckReturnsNoMessageWhenTaskWasExecutedRecently(): void
    {
        $this->connection
            ->shouldReceive('fetchOne')
            ->once()
            ->with('SELECT last_run FROM yap_scheduler LIMIT 1')
            ->andReturn(
                (new \DateTimeImmutable('-23 hours'))
                    ->format('Y-m-d H:i:s'),
            );

        $messages = $this->taskExecution->check();

        $this->assertSame([], $messages);
    }

    public function testCheckReturnsWarningWhenLastRunIsNull(): void
    {
        $this->connection
            ->shouldReceive('fetchOne')
            ->once()
            ->with('SELECT last_run FROM yap_scheduler LIMIT 1')
            ->andReturn(null);

        $messages = $this->taskExecution->check();

        $this->assertEquals(
            [
                new SystemStatusMessage(
                    MessageType::Warning,
                    new TranslatableMessage(
                        'yap_scheduler.tasks.warning.not_active',
                        [],
                        'scheduler',
                    ),
                ),
            ],
            $messages,
        );
    }
}
