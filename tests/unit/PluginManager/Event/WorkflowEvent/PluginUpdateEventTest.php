<?php

declare(strict_types=1);

namespace App\Tests\unit\PluginManager\Event\WorkflowEvent;

use App\Entity\Plugin\Plugin;
use App\PluginManager\Event\WorkflowEvent\PluginUpdateEvent;
use App\PluginManager\Plugin\PluginInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PluginUpdateEvent::class)]
class PluginUpdateEventTest extends TestCase
{
    public function testConstruct(): void
    {
        $pluginInstanceMock = \Mockery::mock(PluginInterface::class);
        $pluginEntityMock = \Mockery::mock(Plugin::class);

        $event = new PluginUpdateEvent(
            $pluginInstanceMock,
            $pluginEntityMock
        );

        $this->assertEquals($pluginInstanceMock, $event->getPluginInstance());
        $this->assertEquals($pluginEntityMock, $event->getPluginEntity());
        $this->assertNull($event->getFromVersion());

        $event->setFromVersion('0.9.0');
        $this->assertEquals('0.9.0', $event->getFromVersion());
    }
}
