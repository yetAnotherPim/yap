<?php

declare(strict_types=1);

namespace App\Tests\integration\PluginManager\DependencyInjection\CompilerPass;

use App\Kernel;
use App\PluginManager\DependencyInjection\CompilerPass\PluginLoader;
use App\PluginManager\Plugin\PluginInterface;
use App\PluginManager\PluginManager;
use App\plugins\SamplePlugin\Command\SampleCommand;
use App\plugins\SamplePlugin\Controller\SampleController;
use App\plugins\SamplePlugin\Decoration\ServiceDecoration;
use App\plugins\SamplePlugin\Entity\SampleEntity;
use App\plugins\SamplePlugin\Plugin;
use App\plugins\SamplePlugin\Repository\SampleRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(PluginLoader::class)]
#[UsesClass(Kernel::class)]
#[UsesClass(PluginManager::class)]
class PluginLoaderTest extends KernelTestCase
{
    public function setUp(): void
    {
        new Filesystem()->remove(__DIR__.'/../../../../../var/cache/test');

        parent::setUp();
    }

    public function testInactiveDumpedPluginIsNotLoadedAfterCompilation(): void
    {
        file_put_contents(__DIR__.'/../../../../../var/plugin_configuration/test_plugin_configuration', '');

        self::bootKernel();
        $container = static::getContainer();

        $pluginInstance = $container->get(Plugin::class);
        $this->assertInstanceOf(PluginInterface::class, $pluginInstance);

        $this->assertTrue($container->getParameter('plugins_loaded_successfully'));

        $this->assertArrayNotHasKey(SampleCommand::class, $container->getRemovedIds());
        $this->assertArrayNotHasKey(SampleController::class, $container->getRemovedIds());
        $this->assertArrayNotHasKey(ServiceDecoration::class, $container->getRemovedIds());
        $this->assertArrayNotHasKey(SampleEntity::class, $container->getRemovedIds());
        $this->assertArrayNotHasKey(SampleRepository::class, $container->getRemovedIds());

        $this->assertFalse($container->has(SampleCommand::class));
        $this->assertFalse($container->has(SampleController::class));
        $this->assertFalse($container->has(ServiceDecoration::class));
        $this->assertFalse($container->has(SampleEntity::class));
        $this->assertFalse($container->has(SampleRepository::class));
    }

    public function testActiveDumpedPluginIsLoadedAfterCompilation(): void
    {
        file_put_contents(__DIR__.'/../../../../../var/plugin_configuration/test_plugin_configuration', 'SamplePlugin');

        self::bootKernel();
        $container = static::getContainer();

        $pluginInstance = $container->get(Plugin::class);
        $this->assertInstanceOf(PluginInterface::class, $pluginInstance);

        $this->assertTrue($container->getParameter('plugins_loaded_successfully'));

        /* services aren't public, but if they have been removed, definitions have been loaded successfully */
        $this->assertArrayHasKey(SampleCommand::class, $container->getRemovedIds());
        $this->assertArrayHasKey(SampleController::class, $container->getRemovedIds());
        $this->assertArrayHasKey(ServiceDecoration::class, $container->getRemovedIds());
        $this->assertArrayHasKey(SampleEntity::class, $container->getRemovedIds());
        $this->assertArrayHasKey(SampleRepository::class, $container->getRemovedIds());
    }
}
