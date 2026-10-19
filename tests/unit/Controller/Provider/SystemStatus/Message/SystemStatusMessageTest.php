<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Provider\SystemStatus\Message;

use App\Controller\Provider\SystemStatus\Message\Enum\MessageType;
use App\Controller\Provider\SystemStatus\Message\SystemStatusMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatableInterface;

#[CoversClass(SystemStatusMessage::class)]
class SystemStatusMessageTest extends TestCase
{
    public function testGettersReturnConstructorValues(): void
    {
        $type = MessageType::Warning;
        $translatable = \Mockery::mock(TranslatableInterface::class);

        $message = new SystemStatusMessage($type, $translatable);

        $this->assertSame($type, $message->getType());
        $this->assertSame($translatable, $message->getMessage());
    }
}
