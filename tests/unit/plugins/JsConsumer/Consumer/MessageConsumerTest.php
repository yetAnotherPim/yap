<?php

declare(strict_types=1);

namespace App\Tests\unit\plugins\JsConsumer\Consumer;

use App\plugins\JsConsumer\Consumer\MessageConsumer;
use App\plugins\JsConsumer\Exception\MessageConsumptionException;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;

#[CoversClass(MessageConsumer::class)]
final class MessageConsumerTest extends MockeryTestCase
{
    public function testConsumeOneReturnsWhenQueueIsEmpty(): void
    {
        $receiver = \Mockery::mock(ReceiverInterface::class);
        $receiver
            ->shouldReceive('get')
            ->once()
            ->andReturn([]);

        $bus = \Mockery::mock(MessageBusInterface::class);
        $eventDispatcher = new EventDispatcher();
        $logger = \Mockery::mock(LoggerInterface::class)->shouldIgnoreMissing();

        $consumer = new MessageConsumer(
            $receiver,
            $bus,
            $eventDispatcher,
            $logger,
        );

        $consumer->consumeOne();

        $this->assertSame(
            [],
            $eventDispatcher->getListeners(WorkerRunningEvent::class),
        );

        $this->assertSame(
            [],
            $eventDispatcher->getListeners(WorkerMessageFailedEvent::class),
        );
    }

    public function testConsumeOneThrowsMessageConsumptionExceptionWhenMessageFails(): void
    {
        $receiver = \Mockery::mock(ReceiverInterface::class);
        $receiver
            ->shouldReceive('get')
            ->once()
            ->andReturn([
                new \Symfony\Component\Messenger\Envelope(new \stdClass()),
            ]);

        $receiver
            ->shouldReceive('reject')
            ->once();

        $bus = \Mockery::mock(MessageBusInterface::class);
        $bus
            ->shouldReceive('dispatch')
            ->once()
            ->andThrow(new \RuntimeException('Handler failed.'));

        $eventDispatcher = new EventDispatcher();
        $logger = \Mockery::mock(LoggerInterface::class)->shouldIgnoreMissing();

        $consumer = new MessageConsumer(
            $receiver,
            $bus,
            $eventDispatcher,
            $logger,
        );

        try {
            $consumer->consumeOne();

            $this->fail('Expected a MessageConsumptionException.');
        } catch (MessageConsumptionException $exception) {
            $this->assertNotNull($exception->getPrevious());
        }

        $this->assertSame(
            [],
            $eventDispatcher->getListeners(WorkerRunningEvent::class),
        );

        $this->assertSame(
            [],
            $eventDispatcher->getListeners(WorkerMessageFailedEvent::class),
        );
    }

    public function testConsumeOneWrapsUnexpectedException(): void
    {
        $receiver = \Mockery::mock(ReceiverInterface::class);
        $bus = \Mockery::mock(MessageBusInterface::class);
        $eventDispatcher = new EventDispatcher();
        $logger = \Mockery::mock(LoggerInterface::class)->shouldIgnoreMissing();

        $cause = new \RuntimeException('Worker failed.');

        $eventDispatcher->addListener(
            WorkerStartedEvent::class,
            static function (WorkerStartedEvent $event) use ($cause): void {
                throw $cause;
            },
        );

        $consumer = new MessageConsumer(
            $receiver,
            $bus,
            $eventDispatcher,
            $logger,
        );

        try {
            $consumer->consumeOne();

            $this->fail('Expected a MessageConsumptionException.');
        } catch (MessageConsumptionException $exception) {
            $this->assertSame($cause, $exception->getPrevious());
        }

        $this->assertSame(
            [],
            $eventDispatcher->getListeners(WorkerRunningEvent::class),
        );

        $this->assertSame(
            [],
            $eventDispatcher->getListeners(WorkerMessageFailedEvent::class),
        );
    }
}
