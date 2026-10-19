<?php

declare(strict_types=1);

namespace App\Messenger\QueueMonitor;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

class QueueMonitor
{
    /**
     * @param ServiceProviderInterface<ReceiverInterface> $receivers
     */
    public function __construct(
        #[Autowire(service: 'messenger.receiver_locator')]
        private readonly ServiceProviderInterface $receivers,
    ) {
    }

    public function getMessageCount(string $transportName): ?int
    {
        if (!$this->receivers->has($transportName)) {
            return null;
        }

        $receiver = $this->receivers->get($transportName);

        if (!$receiver instanceof MessageCountAwareInterface) {
            return null;
        }

        return $receiver->getMessageCount();
    }
}
