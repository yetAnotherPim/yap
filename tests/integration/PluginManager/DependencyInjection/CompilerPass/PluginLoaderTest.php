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
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Kernel\KernelInterface;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(PluginLoader::class)]
#[CoversClass(PluginManager::class)]
#[CoversClass(Kernel::class)]
class PluginLoaderTest extends KernelTestCase
{
    private const string DUMPFILE_PATH = __DIR__.'/../../../../../var/plugin_configuration/test_plugin_configuration';
    private const string VAR_PATH = __DIR__.'/../../../../../var/';

    public function setUp(): void
    {
        new Filesystem()->remove(self::VAR_PATH.'cache/test');

        parent::setUp();
    }

    public function testInactiveDumpedPluginIsNotLoadedAfterCompilation(): void
    {
        file_put_contents(self::DUMPFILE_PATH, '');

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
        file_put_contents(self::DUMPFILE_PATH, 'SamplePlugin');

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

    public function testLoadActivePluginsWithNoDumpfile(): void
    {
        new Filesystem()->remove(self::VAR_PATH.'plugin_configuration/nd_plugin_configuration');
        new Filesystem()->remove(self::VAR_PATH.'cache/nd');

        self::bootKernel([
            'environment' => 'nd',
        ]);

        $this->assertInstanceOf(KernelInterface::class, self::$kernel);
        $this->assertFalse(self::$kernel->getContainer()->hasParameter('plugins_loaded_successfully'));
    }

    public function testLoadActivePluginsWithUnreadableDumpfile(): void
    {
        new Filesystem()->touch(self::VAR_PATH.'plugin_configuration/ud_plugin_configuration');
        new Filesystem()->chmod(self::VAR_PATH.'/plugin_configuration/ud_plugin_configuration', 222);
        new Filesystem()->remove(self::VAR_PATH.'cache/ud');

        self::bootKernel([
            'environment' => 'ud',
        ]);

        $this->assertInstanceOf(KernelInterface::class, self::$kernel);
        $this->assertFalse(self::$kernel->getContainer()->hasParameter('plugins_loaded_successfully'));
    }

    public function testLoadActivePluginsWithMissingServiceDefinitionFile(): void
    {
        file_put_contents(self::VAR_PATH.'plugin_configuration/msdf_plugin_configuration', 'TestPlugin');

        self::bootKernel([
            'environment' => 'msdf',
        ]);

        $this->assertInstanceOf(KernelInterface::class, self::$kernel);
        $this->assertTrue(self::$kernel->getContainer()->hasParameter('plugins_loaded_successfully'));
    }
}
