<?php

declare(strict_types=1);

namespace App\Tests\unit\Messenger\QueueMonitor;

use App\Messenger\QueueMonitor\QueueMonitor;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use Symfony\Contracts\Service\ServiceProviderInterface;

#[CoversClass(QueueMonitor::class)]
class QueueMonitorTest extends MockeryTestCase
{
    /** @var ServiceProviderInterface<ReceiverInterface>&MockInterface */
    private ServiceProviderInterface $receivers;

    private QueueMonitor $queueMonitor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->receivers = \Mockery::mock(ServiceProviderInterface::class);
        $this->queueMonitor = new QueueMonitor($this->receivers);
    }

    public function testGetMessageCountReturnsNullWhenTransportDoesNotExist(): void
    {
        $this->receivers
            ->shouldReceive('has')
            ->once()
            ->with('unknown')
            ->andReturn(false);

        $this->receivers->shouldNotReceive('get');

        $this->assertNull(
            $this->queueMonitor->getMessageCount('unknown'),
        );
    }

    public function testGetMessageCountReturnsNullWhenReceiverDoesNotSupportMessageCount(): void
    {
        $receiver = \Mockery::mock(ReceiverInterface::class);

        $this->receivers
            ->shouldReceive('has')
            ->once()
            ->with('async')
            ->andReturn(true);

        $this->receivers
            ->shouldReceive('get')
            ->once()
            ->with('async')
            ->andReturn($receiver);

        $this->assertNull(
            $this->queueMonitor->getMessageCount('async'),
        );
    }

    public function testGetMessageCountReturnsReceiverMessageCount(): void
    {
        $receiver = \Mockery::mock(MessageCountAwareInterface::class);

        $receiver
            ->shouldReceive('getMessageCount')
            ->once()
            ->andReturn(42);

        $this->receivers
            ->shouldReceive('has')
            ->once()
            ->with('product_update')
            ->andReturn(true);

        $this->receivers
            ->shouldReceive('get')
            ->once()
            ->with('product_update')
            ->andReturn($receiver);

        $this->assertSame(
            42,
            $this->queueMonitor->getMessageCount('product_update'),
        );
    }

    public function testGetMessageCountReturnsZeroWhenQueueIsEmpty(): void
    {
        $receiver = \Mockery::mock(MessageCountAwareInterface::class);

        $receiver
            ->shouldReceive('getMessageCount')
            ->once()
            ->andReturn(0);

        $this->receivers
            ->shouldReceive('has')
            ->once()
            ->with('product_update')
            ->andReturn(true);

        $this->receivers
            ->shouldReceive('get')
            ->once()
            ->with('product_update')
            ->andReturn($receiver);

        $this->assertSame(
            0,
            $this->queueMonitor->getMessageCount('product_update'),
        );
    }
}
