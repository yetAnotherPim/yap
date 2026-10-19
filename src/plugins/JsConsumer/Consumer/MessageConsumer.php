<?php

declare(strict_types=1);

namespace App\plugins\JsConsumer\Consumer;

use App\plugins\JsConsumer\Exception\MessageConsumptionException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Component\Messenger\Worker;

class MessageConsumer
{
    public function __construct(
        #[Autowire(service: 'messenger.transport.product_update')]
        private readonly ReceiverInterface $receiver,
        private readonly MessageBusInterface $bus,
        private readonly EventDispatcherInterface $eventDispatcher,
        #[Autowire(service: 'js_consumer.logger')]
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws MessageConsumptionException
     */
    public function consumeOne(): void
    {
        $failure = null;

        $stopWorker = static function (WorkerRunningEvent $event): void {
            $event->getWorker()->stop();
        };

        $captureFailure = static function (
            WorkerMessageFailedEvent $event,
        ) use (&$failure): void {
            $failure ??= $event->getThrowable();
        };

        $this->eventDispatcher->addListener(
            WorkerRunningEvent::class,
            $stopWorker,
        );

        $this->eventDispatcher->addListener(
            WorkerMessageFailedEvent::class,
            $captureFailure,
        );

        try {
            $worker = new Worker(
                ['product_update' => $this->receiver],
                $this->bus,
                $this->eventDispatcher,
                $this->logger,
            );

            $worker->run(['sleep' => 0, 'time_limit' => 1]);
        } catch (\Throwable $exception) {
            $failure ??= $exception;
        } finally {
            $this->eventDispatcher->removeListener(
                WorkerRunningEvent::class,
                $stopWorker,
            );

            $this->eventDispatcher->removeListener(
                WorkerMessageFailedEvent::class,
                $captureFailure,
            );
        }

        if (null !== $failure) {
            $this->logger->error(
                'Failed to consume a product update message.',
                ['exception' => $failure],
            );

            throw new MessageConsumptionException('Failed to consume a product update message.', 0, $failure);
        }
    }
}
