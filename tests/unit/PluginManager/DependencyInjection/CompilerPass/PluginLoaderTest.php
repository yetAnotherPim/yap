<?php

declare(strict_types=1);

namespace App\Tests\unit\PluginManager\DependencyInjection\CompilerPass;

use App\PluginManager\DependencyInjection\CompilerPass\PluginLoader;
use App\PluginManager\PluginManager;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[CoversClass(PluginLoader::class)]
#[UsesClass(PluginManager::class)]
class PluginLoaderTest extends MockeryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testProcess(): void
    {
        $appEnv = 'test';

        $container = $this->createContainerMock($appEnv);
        $container
            ->shouldReceive('setParameter')
            ->never();

        new PluginLoader()->process($container);
    }

    private function createContainerMock(string $appEnv): ContainerBuilder&Mockery\MockInterface
    {
        $parameterBag = \Mockery::mock(ParameterBagInterface::class);
        $parameterBag
            ->shouldReceive('get')
            ->with('kernel.environment')
            ->andReturn($appEnv);

        $container = \Mockery::mock(ContainerBuilder::class);
        $container
            ->shouldReceive('getParameterBag')
            ->andReturn($parameterBag);
        $container
            ->shouldReceive('getParameter')
            ->with('kernel.project_dir')
            ->andReturn('');
        $container
            ->shouldReceive('getParameter')
            ->with('plugin_configuration_folder')
            ->andReturn('');

        return $container;
    }
}
