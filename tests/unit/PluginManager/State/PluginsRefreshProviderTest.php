<?php

declare(strict_types=1);

namespace App\Tests\unit\PluginManager\State;

use ApiPlatform\Metadata\Operation;
use App\PluginManager\Cache\CacheClearer;
use App\PluginManager\PluginManager;
use App\PluginManager\State\PluginsRefreshProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PluginsRefreshProvider::class)]
class PluginsRefreshProviderTest extends MockeryTestCase
{
    public function testProvideClearsCacheAndReturnsPlugins(): void
    {
        $plugins = new ArrayCollection();

        $cacheClearer = \Mockery::mock(CacheClearer::class);
        $cacheClearer
            ->shouldReceive('clear')
            ->once();

        $pluginManager = \Mockery::mock(PluginManager::class);
        $pluginManager
            ->shouldReceive('getPlugins')
            ->once()
            ->andReturn($plugins);

        $operation = \Mockery::mock(Operation::class);

        $provider = new PluginsRefreshProvider($cacheClearer, $pluginManager);

        $result = $provider->provide($operation);

        self::assertSame($plugins, $result);
    }
}
