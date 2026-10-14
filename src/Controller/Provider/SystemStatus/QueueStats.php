<?php

declare(strict_types=1);

namespace App\Controller\Provider\SystemStatus;

use App\Controller\Provider\SystemStatus\Message\Enum\MessageType;
use App\Controller\Provider\SystemStatus\Message\SystemStatusMessage;
use App\Messenger\QueueMonitor\QueueMonitor;
use Symfony\Component\Translation\TranslatableMessage;

class QueueStats implements SystemCheckInterface
{
    public function __construct(
        private readonly QueueMonitor $queueMonitor, )
    {
    }

    /**
     * @return list<SystemStatusMessage>
     */
    public function check(): array
    {
        $messages = [];

        $failedProductUpdates = (int) $this->queueMonitor->getMessageCount('product_update_failed');

        if ($failedProductUpdates > 0) {
            $messages[] =
                new SystemStatusMessage(
                    MessageType::Warning,
                    new TranslatableMessage(
                        'queue.notice.product_update_failed',
                        [
                            '%count%' => $failedProductUpdates,
                        ],
                        'admin',
                    )
                );
        }

        return $messages;
    }
}
