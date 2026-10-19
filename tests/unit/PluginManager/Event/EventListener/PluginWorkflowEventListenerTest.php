<?php

declare(strict_types=1);

namespace App\Tests\unit\PluginManager\Event\EventListener;

use App\Entity\Plugin\Plugin;
use App\PluginManager\Cache\CacheClearer;
use App\PluginManager\Event\WorkflowEvent\EventListener\PluginWorkflowEventListener;
use App\PluginManager\Event\WorkflowEvent\Exception\PluginWorkflowException;
use App\PluginManager\Event\WorkflowEvent\PluginActivateEvent;
use App\PluginManager\Event\WorkflowEvent\PluginDeactivateEvent;
use App\PluginManager\Event\WorkflowEvent\PluginUpdateEvent;
use App\PluginManager\Plugin\PluginInterface;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Log\LoggerInterface;

#[CoversClass(PluginWorkflowEventListener::class)]
#[UsesClass(PluginWorkflowException::class)]
class PluginWorkflowEventListenerTest extends MockeryTestCase
{
    /** @phpstan-var LoggerInterface|MockInterface  */
    private LoggerInterface $loggerMock;

    /** @phpstan-var CacheClearer|MockInterface  */
    private CacheClearer $cacheClearer;

    public function setUp(): void
    {
        $this->loggerMock = \Mockery::mock(LoggerInterface::class);
        $this->cacheClearer = \Mockery::mock(CacheClearer::class);

        parent::setUp();
    }

    /**
     * @throws PluginWorkflowException
     */
    public function testOnActivate(): void
    {
        $pluginEntityMock = \Mockery::mock(Plugin::class);

        $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $entityManagerMock
            ->shouldReceive('persist')
            ->once()
            ->with($pluginEntityMock);

        $entityManagerMock
            ->shouldReceive('flush')
            ->once();

        $this->cacheClearer
            ->shouldReceive('clear')
            ->once();

        $pluginInstanceMock = \Mockery::mock(PluginInterface::class);
        $pluginInstanceMock
            ->shouldReceive('activate')
            ->once()
            ->with($entityManagerMock)
            ->andReturn($pluginInstanceMock);

        $pluginInstanceMock
            ->shouldReceive('getVersion')
            ->once()
            ->andReturn('1.0.0');

        $pluginEntityMock
            ->shouldReceive('setActive')
            ->once()
            ->with(true)
            ->andReturn($pluginEntityMock);

        $pluginEntityMock
            ->shouldReceive('setInstalledVersion')
            ->once()
            ->with('1.0.0')
            ->andReturn($pluginEntityMock);

        $listener = new PluginWorkflowEventListener($entityManagerMock, $this->cacheClearer, $this->loggerMock);

        $eventMock = \Mockery::mock(PluginActivateEvent::class);

        $eventMock
            ->shouldReceive('getPluginInstance')
            ->once()
            ->andReturn($pluginInstanceMock);

        $eventMock
            ->shouldReceive('getPluginEntity')
            ->once()
            ->andReturn($pluginEntityMock);

        $this->loggerMock
            ->shouldReceive('error')
            ->never();

        $listener->onActivate($eventMock);
    }

    /**
     * @throws PluginWorkflowException
     */
    public function testOnActivateCausingWorkflowException(): void
    {
        $pluginEntityMock = \Mockery::mock(Plugin::class);

        $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $entityManagerMock
            ->shouldReceive('persist')
            ->never();

        $entityManagerMock
            ->shouldReceive('flush')
            ->never();

        $this->cacheClearer
            ->shouldReceive('clear')
            ->never();

        $pluginInstanceMock = \Mockery::mock(PluginInterface::class);
        $pluginInstanceMock
            ->shouldReceive('activate')
            ->once()
            ->with($entityManagerMock)
            ->andThrow(new \RuntimeException('Error'));

        $pluginInstanceMock
            ->shouldReceive('getVersion')
            ->never();

        $pluginEntityMock
            ->shouldReceive('getName')
            ->once()
            ->andReturn('SamplePlugin');

        $pluginEntityMock
            ->shouldReceive('setActive')
            ->never();

        $pluginEntityMock
            ->shouldReceive('setInstalledVersion')
            ->never();

        $listener = new PluginWorkflowEventListener($entityManagerMock, $this->cacheClearer, $this->loggerMock);

        $eventMock = \Mockery::mock(PluginActivateEvent::class);

        $eventMock
            ->shouldReceive('getPluginInstance')
            ->once()
            ->andReturn($pluginInstanceMock);

        $eventMock
            ->shouldReceive('getPluginEntity')
            ->twice()
            ->andReturn($pluginEntityMock);

        $this->expectException(PluginWorkflowException::class);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                'An error occurred at {pluginInstance}::activate()',
                [
                    'pluginInstance' => \get_class($pluginInstanceMock),
                    'message' => 'Error',
                    'exception' => \get_class(new \RuntimeException()),
                ]
            );

        $listener->onActivate($eventMock);
    }

    /**
     * @throws PluginWorkflowException
     */
    public function testOnDeactivate(): void
    {
        $pluginEntityMock = \Mockery::mock(Plugin::class);

        $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $entityManagerMock
            ->shouldReceive('persist')
            ->once()
            ->with($pluginEntityMock);

        $entityManagerMock
            ->shouldReceive('flush')
            ->once();

        $this->cacheClearer
            ->shouldReceive('clear')
            ->once();

        $pluginInstanceMock = \Mockery::mock(PluginInterface::class);
        $pluginInstanceMock
            ->shouldReceive('deactivate')
            ->once()
            ->with($entityManagerMock)
            ->andReturn($pluginInstanceMock);

        $pluginEntityMock
            ->shouldReceive('setActive')
            ->once()
            ->with(false)
            ->andReturn($pluginEntityMock);

        $listener = new PluginWorkflowEventListener($entityManagerMock, $this->cacheClearer, $this->loggerMock);

        $eventMock = \Mockery::mock(PluginDeactivateEvent::class);

        $eventMock
            ->shouldReceive('getPluginInstance')
            ->once()
            ->andReturn($pluginInstanceMock);

        $eventMock
            ->shouldReceive('getPluginEntity')
            ->once()
            ->andReturn($pluginEntityMock);

        $this->loggerMock
            ->shouldReceive('error')
            ->never();

        $listener->onDeactivate($eventMock);
    }

    /**
     * @throws PluginWorkflowException
     */
    public function testOnDeactivateCausingWorkflowException(): void
    {
        $pluginEntityMock = \Mockery::mock(Plugin::class);

        $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $entityManagerMock
            ->shouldReceive('persist')
            ->never();

        $entityManagerMock
            ->shouldReceive('flush')
            ->never();

        $this->cacheClearer
            ->shouldReceive('clear')
            ->never();

        $pluginInstanceMock = \Mockery::mock(PluginInterface::class);
        $pluginInstanceMock
            ->shouldReceive('deactivate')
            ->once()
            ->with($entityManagerMock)
            ->andThrow(new \RuntimeException('Error'));

        $pluginInstanceMock
            ->shouldReceive('getVersion')
            ->never();

        $pluginEntityMock
            ->shouldReceive('getName')
            ->once()
            ->andReturn('SamplePlugin');

        $pluginEntityMock
            ->shouldReceive('setActive')
            ->never();

        $listener = new PluginWorkflowEventListener($entityManagerMock, $this->cacheClearer, $this->loggerMock);

        $eventMock = \Mockery::mock(PluginDeactivateEvent::class);

        $eventMock
            ->shouldReceive('getPluginInstance')
            ->once()
            ->andReturn($pluginInstanceMock);

        $eventMock
            ->shouldReceive('getPluginEntity')
            ->twice()
            ->andReturn($pluginEntityMock);

        $this->expectException(PluginWorkflowException::class);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                'An error occurred at {pluginInstance}::deactivate()',
                [
                    'pluginInstance' => \get_class($pluginInstanceMock),
                    'message' => 'Error',
                    'exception' => \get_class(new \RuntimeException()),
                ]
            );

        $listener->onDeactivate($eventMock);
    }

    /**
     * @throws PluginWorkflowException
     */
    public function testOnUpdate(): void
    {
        $pluginEntityMock = \Mockery::mock(Plugin::class);

        $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $entityManagerMock
            ->shouldReceive('persist')
            ->once()
            ->with($pluginEntityMock);

        $entityManagerMock
            ->shouldReceive('flush')
            ->once();

        $this->cacheClearer
            ->shouldReceive('clear')
            ->once();

        $pluginInstanceMock = \Mockery::mock(PluginInterface::class);
        $pluginInstanceMock
            ->shouldReceive('update')
            ->once()
            ->with('0.9.0', $entityManagerMock)
            ->andReturn($pluginInstanceMock);

        $pluginInstanceMock
            ->shouldReceive('getVersion')
            ->once()
            ->andReturn('1.0.0');

        $pluginEntityMock
            ->shouldReceive('getInstalledVersion')
            ->times(3)
            ->andReturn('0.9.0');

        $pluginEntityMock
            ->shouldReceive('setInstalledVersion')
            ->once()
            ->with('1.0.0')
            ->andReturn($pluginEntityMock);

        $pluginEntityMock
            ->shouldReceive('setActive')
            ->once()
            ->with(true)
            ->andReturn($pluginEntityMock);

        $listener = new PluginWorkflowEventListener($entityManagerMock, $this->cacheClearer, $this->loggerMock);

        $eventMock = \Mockery::mock(PluginUpdateEvent::class);

        $eventMock
            ->shouldReceive('getPluginInstance')
            ->once()
            ->andReturn($pluginInstanceMock);

        $eventMock
            ->shouldReceive('getPluginEntity')
            ->once()
            ->andReturn($pluginEntityMock);

        $eventMock
            ->shouldReceive('setFromVersion')
            ->once()
            ->with('0.9.0');

        $this->loggerMock
            ->shouldReceive('error')
            ->never();

        $listener->onUpdate($eventMock);
    }

    /**
     * @throws PluginWorkflowException
     */
    public function testOnUpdateCausingWorkflowException(): void
    {
        $pluginEntityMock = \Mockery::mock(Plugin::class);

        $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $entityManagerMock
            ->shouldReceive('persist')
            ->never();

        $entityManagerMock
            ->shouldReceive('flush')
            ->never();

        $this->cacheClearer
            ->shouldReceive('clear')
            ->never();

        $pluginInstanceMock = \Mockery::mock(PluginInterface::class);
        $pluginInstanceMock
            ->shouldReceive('update')
            ->once()
            ->with('0.9.0', $entityManagerMock)
            ->andThrow(new \RuntimeException('Error'));

        $pluginInstanceMock
            ->shouldReceive('getVersion')
            ->once()
            ->andReturn('1.0.0');

        $pluginEntityMock
            ->shouldReceive('getName')
            ->once()
            ->andReturn('SamplePlugin');

        $pluginEntityMock
            ->shouldReceive('getInstalledVersion')
            ->times(3)
            ->andReturn('0.9.0');

        $pluginEntityMock
            ->shouldReceive('setInstalledVersion')
            ->never();

        $listener = new PluginWorkflowEventListener($entityManagerMock, $this->cacheClearer, $this->loggerMock);

        $eventMock = \Mockery::mock(PluginUpdateEvent::class);

        $eventMock
            ->shouldReceive('getPluginInstance')
            ->once()
            ->andReturn($pluginInstanceMock);

        $eventMock
            ->shouldReceive('getPluginEntity')
            ->twice()
            ->andReturn($pluginEntityMock);

        $eventMock
            ->shouldReceive('setFromVersion')
            ->once()
            ->with('0.9.0');

        $eventMock
            ->shouldReceive('getFromVersion')
            ->once()
            ->andReturn('0.9.0');

        $this->expectException(PluginWorkflowException::class);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                'An error occurred at {pluginInstance}::update()',
                [
                    'pluginInstance' => \get_class($pluginInstanceMock),
                    'fromVersion' => '0.9.0',
                    'toVersion' => '1.0.0',
                    'message' => 'Error',
                    'exception' => \get_class(new \RuntimeException()),
                ]
            );

        $listener->onUpdate($eventMock);
    }

    /**
     * @throws PluginWorkflowException
     */
    public function testOnUpdateNotInstalled(): void
    {
        $pluginEntityMock = \Mockery::mock(Plugin::class);

        $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $entityManagerMock
            ->shouldReceive('persist')
            ->never();

        $entityManagerMock
            ->shouldReceive('flush')
            ->never();

        $this->cacheClearer
            ->shouldReceive('clear')
            ->never();

        $pluginInstanceMock = \Mockery::mock(PluginInterface::class);
        $pluginInstanceMock
            ->shouldReceive('update')
            ->never();

        $pluginInstanceMock
            ->shouldReceive('getVersion')
            ->once()
            ->andReturn('1.0.0');

        $pluginEntityMock
            ->shouldReceive('getName')
            ->once()
            ->andReturn('SamplePlugin');

        $pluginEntityMock
            ->shouldReceive('getInstalledVersion')
            ->once()
            ->andReturn(null);

        $pluginEntityMock
            ->shouldReceive('setInstalledVersion')
            ->never();

        $listener = new PluginWorkflowEventListener($entityManagerMock, $this->cacheClearer, $this->loggerMock);

        $eventMock = \Mockery::mock(PluginUpdateEvent::class);

        $eventMock
            ->shouldReceive('getPluginInstance')
            ->once()
            ->andReturn($pluginInstanceMock);

        $eventMock
            ->shouldReceive('getPluginEntity')
            ->twice()
            ->andReturn($pluginEntityMock);

        $eventMock
            ->shouldReceive('setFromVersion')
            ->never();

        $this->expectException(PluginWorkflowException::class);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                'Plugin {pluginInstance} is not installed',
                [
                    'pluginInstance' => \get_class($pluginInstanceMock),
                    'toVersion' => '1.0.0',
                ]
            );

        $listener->onUpdate($eventMock);
    }
}
