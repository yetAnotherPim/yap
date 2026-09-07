<?php

declare(strict_types=1);

namespace App\Controller\Provider\SystemStatus\Message;

use App\Controller\Provider\SystemStatus\Message\Enum\MessageType;
use Symfony\Contracts\Translation\TranslatableInterface;

class SystemStatusMessage
{
    public function __construct(
        private readonly MessageType $type,
        private readonly TranslatableInterface $message,
    ) {
    }

    public function getType(): MessageType
    {
        return $this->type;
    }

    public function getMessage(): TranslatableInterface
    {
        return $this->message;
    }
}
