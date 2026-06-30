<?php

declare(strict_types=1);

namespace App\Tests\unit\PluginManager\Cache\Decorator;

use App\PluginManager\Cache\Decorator\CacheClearerDecorator;
use App\PluginManager\PluginManager;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

#[CoversClass(CacheClearerDecorator::class)]
class CacheClearerDecoratorTest extends MockeryTestCase
{
    public function testClearDumpsPluginConfigurationAndDelegatesToInnerClearer(): void
    {
        $cacheDir = '/var/cache/test';

        $pluginManager = \Mockery::mock(PluginManager::class);
        $pluginManager
            ->shouldReceive('dumpPluginConfiguration')
            ->once()
            ->andReturn(true);

        $inner = \Mockery::mock(CacheClearerInterface::class);
        $inner
            ->shouldReceive('clear')
            ->once()
            ->with($cacheDir);

        $decorator = new CacheClearerDecorator($pluginManager, $inner);

        $decorator->clear($cacheDir);
    }
}
