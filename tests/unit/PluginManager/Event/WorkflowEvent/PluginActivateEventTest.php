<?php

declare(strict_types=1);

namespace App\Tests\unit\PluginManager\Event\WorkflowEvent;

use App\Entity\Plugin\Plugin;
use App\PluginManager\Event\WorkflowEvent\PluginActivateEvent;
use App\PluginManager\Plugin\PluginInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PluginActivateEvent::class)]
class PluginActivateEventTest extends TestCase
{
    public function testConstruct(): void
    {
        $pluginInstanceMock = \Mockery::mock(PluginInterface::class);
        $pluginEntityMock = \Mockery::mock(Plugin::class);

        $event = new PluginActivateEvent(
            $pluginInstanceMock,
            $pluginEntityMock
        );

        $this->assertEquals($pluginInstanceMock, $event->getPluginInstance());
        $this->assertEquals($pluginEntityMock, $event->getPluginEntity());
    }
}
