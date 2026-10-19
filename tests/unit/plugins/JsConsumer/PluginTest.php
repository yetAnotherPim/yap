<?php

declare(strict_types=1);

namespace App\Tests\unit\plugins\JsConsumer;

use App\plugins\JsConsumer\Plugin;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Plugin::class)]
class PluginTest extends MockeryTestCase
{
    public function testGetNameReturnsPluginName(): void
    {
        $plugin = new Plugin();

        $this->assertSame('JsConsumer', $plugin->getName());
    }

    public function testGetVersionReturnsPluginVersion(): void
    {
        $plugin = new Plugin();

        $this->assertSame('1.0.0', $plugin->getVersion());
    }

    public function testActivateCreatesDatabaseSchemaAndReturnsSelf(): void
    {
        $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $entityManagerMock
            ->shouldNotReceive('getConnection');

        $plugin = new Plugin();

        $this->assertSame($plugin, $plugin->activate($entityManagerMock));
    }

    public function testDeactivateReturnsSelfWithoutDatabaseInteraction(): void
    {
        $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $entityManagerMock
            ->shouldNotReceive('getConnection');

        $plugin = new Plugin();

        $this->assertSame($plugin, $plugin->deactivate($entityManagerMock));
    }

    public function testUpdate(): void
    {
        $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $entityManagerMock
            ->shouldNotReceive('getConnection');

        $plugin = new Plugin();

        $this->assertSame($plugin, $plugin->update('0.8.0', $entityManagerMock));
    }
}
