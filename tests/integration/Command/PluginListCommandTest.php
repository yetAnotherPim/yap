<?php

declare(strict_types=1);

namespace App\Tests\integration\Command;

use App\Controller\Api\PluginWorkflowController;
use App\Entity\Plugin\Plugin;
use App\Kernel;
use App\PluginManager\Cache\CacheClearer;
use App\PluginManager\Cache\Decorator\CacheClearerDecorator;
use App\PluginManager\Command\PluginsListCommand;
use App\PluginManager\DependencyInjection\CompilerPass\PluginLoader;
use App\PluginManager\Event\Enum\Action;
use App\PluginManager\Event\WorkflowEvent\EventListener\PluginWorkflowEventListener;
use App\PluginManager\Event\WorkflowEvent\Exception\PluginWorkflowException;
use App\PluginManager\PluginManager;
use App\PluginManager\State\PluginsRefreshProvider;
use App\Repository\PluginRepository;
use App\Tests\TestData\Factory\Plugin\PluginFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;
use Zenstruck\Foundry\Test\Factories;

#[CoversClass(PluginsListCommand::class)]
#[CoversClass(Plugin::class)]
#[CoversClass(PluginWorkflowEventListener::class)]
#[CoversClass(PluginWorkflowController::class)]
#[CoversClass(PluginManager::class)]
#[CoversClass(PluginLoader::class)]
#[CoversClass(PluginWorkflowException::class)]
#[CoversClass(CacheClearer::class)]
#[CoversClass(CacheClearerDecorator::class)]
#[CoversClass(PluginsRefreshProvider::class)]
#[CoversClass(PluginRepository::class)]
#[CoversClass(Action::class)]
#[CoversClass(Kernel::class)]
#[CoversClass(\App\plugins\SamplePlugin\Plugin::class)]
class PluginListCommandTest extends KernelTestCase
{
    use Factories;

    private ApplicationTester $applicationTester;

    protected function setUp(): void
    {
        self::bootKernel();
        $kernel = self::$kernel;
        $this->assertInstanceOf(Kernel::class, $kernel);
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $this->applicationTester = new ApplicationTester($application);

        parent::setUp();
    }

    public function testPluginList(): void
    {
        $dumpfilePath = __DIR__.'/../../../var/plugin_configuration/test_plugin_configuration';

        file_put_contents($dumpfilePath, '');

        PluginFactory::createOne([
            'name' => 'DeletedPlugin',
            'installedVersion' => '0.8.0',
            'namespace' => '\\App\\plugins\\DeletedPlugin\\Plugin',
            'active' => true,
        ]);
        PluginFactory::createOne([
            'name' => 'SamplePlugin',
            'installedVersion' => '0.8.0',
            'namespace' => 'App\\plugins\\SamplePlugin\\Plugin',
            'active' => true,
        ]);

        $this->applicationTester
            ->run(
                [
                    'command' => 'yap:plugins:list',
                ]
            );

        $this->applicationTester->assertCommandIsSuccessful();
        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('SamplePlugin', $output);
        $this->assertStringContainsString('TestPlugin', $output);
        $this->assertStringNotContainsString('DeletedPlugin', $output);

        $this->applicationTester
            ->run(
                [
                    'command' => 'yap:plugins:list',
                    '--refresh' => true,
                ]
            );

        $this->assertFileExists($dumpfilePath);
        /** @phpstan-var string $dumpFileContent */
        $dumpFileContent = file_get_contents($dumpfilePath);

        $this->assertStringContainsString('SamplePlugin', $dumpFileContent);
        $this->assertStringNotContainsString('TestPlugin', $dumpFileContent);
        $this->assertStringNotContainsString('DeletedPlugin', $dumpFileContent);

        $this->applicationTester->assertCommandIsSuccessful();
        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('[OK] Cache for the "test" environment (debug=true) was successfully cleared.', $output);
        $this->assertStringContainsString('  SamplePlugin   App\plugins\SamplePlugin\Plugin                ✓        0.9.0     0.8.0               ✓', $output);
        $this->assertStringContainsString('  TestPlugin     App\Tests\TestData\plugins\TestPlugin\Plugin   x        0.9.0                         x', $output);
        $this->assertStringNotContainsString('DeletedPlugin', $output);
    }
}
