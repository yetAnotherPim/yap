<?php

declare(strict_types=1);

namespace App\Tests\integration\PluginManager\DependencyInjection;

use App\PluginManager\Plugin\PluginInterface;
use App\PluginManager\PluginManager;
use App\plugins\Scheduler\Controller\Admin\TasksController;
use App\plugins\Scheduler\EventListener\MenuItemsConfiguredListener;
use App\plugins\Scheduler\Plugin;
use App\plugins\Scheduler\Provider\SystemStatus\TaskExecution;
use App\plugins\Scheduler\Provider\TaskProvider;
use App\plugins\Scheduler\Task\CleanUp\RemoveObsoleteFields;
use App\plugins\Scheduler\Task\CleanUp\RemoveObsoleteFieldValueTranslations;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Kernel\KernelInterface;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(PluginManager::class)]
class PluginLoaderTest extends KernelTestCase
{
    private const string DUMPFILE_PATH = __DIR__.'/../../../../var/plugin_configuration/test_plugin_configuration';
    private const string VAR_PATH = __DIR__.'/../../../../var/';

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

        $this->assertArrayNotHasKey(TasksController::class, $container->getRemovedIds());
        $this->assertArrayNotHasKey(TaskProvider::class, $container->getRemovedIds());
        $this->assertArrayNotHasKey(TaskExecution::class, $container->getRemovedIds());
        $this->assertArrayNotHasKey(RemoveObsoleteFields::class, $container->getRemovedIds());
        $this->assertArrayNotHasKey(RemoveObsoleteFieldValueTranslations::class, $container->getRemovedIds());
        $this->assertArrayNotHasKey(MenuItemsConfiguredListener::class, $container->getRemovedIds());

        $this->assertFalse($container->has(TasksController::class));
        $this->assertFalse($container->has(TaskProvider::class));
        $this->assertFalse($container->has(TaskExecution::class));
        $this->assertFalse($container->has(RemoveObsoleteFields::class));
        $this->assertFalse($container->has(RemoveObsoleteFieldValueTranslations::class));
        $this->assertFalse($container->has(MenuItemsConfiguredListener::class));
    }

    public function testActiveDumpedPluginIsLoadedAfterCompilation(): void
    {
        file_put_contents(self::DUMPFILE_PATH, 'Scheduler');

        self::bootKernel();
        $container = static::getContainer();

        $pluginInstance = $container->get(Plugin::class);
        $this->assertInstanceOf(PluginInterface::class, $pluginInstance);

        $this->assertTrue($container->getParameter('plugins_loaded_successfully'));

        /* services except controllers aren't public, but if they have been removed, definitions have been loaded successfully */
        $this->assertContains(TasksController::class, $container->getServiceIds());
        $this->assertArrayHasKey(TaskProvider::class, $container->getRemovedIds());
        $this->assertArrayHasKey(TaskExecution::class, $container->getRemovedIds());
        $this->assertArrayHasKey(RemoveObsoleteFields::class, $container->getRemovedIds());
        $this->assertArrayHasKey(RemoveObsoleteFieldValueTranslations::class, $container->getRemovedIds());
        $this->assertArrayHasKey(MenuItemsConfiguredListener::class, $container->getRemovedIds());
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
