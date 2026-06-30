<?php

declare(strict_types=1);

namespace App\Tests\unit;

use App\Kernel;
use App\PluginManager\DependencyInjection\CompilerPass\PluginLoader;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[CoversClass(Kernel::class)]
class KernelTest extends MockeryTestCase
{
    public function testBuildAddsPluginLoaderCompilerPass(): void
    {
        $container = \Mockery::mock(ContainerBuilder::class);

        $container
            ->shouldReceive('addCompilerPass')
            ->once()
            ->with(\Mockery::type(PluginLoader::class))
            ->andReturn($container);

        $kernel = new Kernel('test', true);

        $kernel->build($container);
    }
}
