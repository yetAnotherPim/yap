<?php

declare(strict_types=1);

namespace App\Tests\integration\PluginManager\Command;

use App\Controller\Api\PluginWorkflowController;
use App\Entity\Plugin\Plugin;
use App\Kernel;
use App\PluginManager\Cache\CacheClearer;
use App\PluginManager\Cache\Decorator\CacheClearerDecorator;
use App\PluginManager\Command\PluginActivateCommand;
use App\PluginManager\Command\PluginsListCommand;
use App\PluginManager\Command\PluginUpdateCommand;
use App\PluginManager\DependencyInjection\CompilerPass\PluginLoader;
use App\PluginManager\Event\Enum\Action;
use App\PluginManager\Event\WorkflowEvent\EventListener\PluginWorkflowEventListener;
use App\PluginManager\Event\WorkflowEvent\Exception\PluginWorkflowException;
use App\PluginManager\Event\WorkflowEvent\PluginActivateEvent;
use App\PluginManager\Event\WorkflowEvent\PluginUpdateEvent;
use App\PluginManager\PluginManager;
use App\PluginManager\State\PluginsRefreshProvider;
use App\Repository\PluginRepository;
use App\Tests\helper\Trait\DatabaseTrait;
use App\Tests\helper\Trait\PluginCommandTrait;
use App\Tests\TestData\Factory\Plugin\PluginFactory;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;
use Zenstruck\Foundry\Test\Factories;

#[CoversClass(PluginUpdateCommand::class)]
#[CoversClass(Plugin::class)]
#[CoversClass(PluginWorkflowEventListener::class)]
#[CoversClass(PluginWorkflowController::class)]
#[CoversClass(PluginManager::class)]
#[CoversClass(PluginLoader::class)]
#[CoversClass(PluginUpdateEvent::class)]
#[CoversClass(PluginWorkflowException::class)]
#[CoversClass(CacheClearer::class)]
#[CoversClass(CacheClearerDecorator::class)]
#[CoversClass(PluginsRefreshProvider::class)]
#[CoversClass(PluginRepository::class)]
#[CoversClass(Action::class)]
#[CoversClass(Kernel::class)]
#[CoversClass(\App\plugins\SamplePlugin\Plugin::class)]
#[UsesClass(PluginsListCommand::class)]
#[UsesClass(PluginActivateCommand::class)]
#[UsesClass(PluginActivateEvent::class)]
#[RunTestsInSeparateProcesses]
class PluginUpdateCommandTest extends KernelTestCase
{
    use Factories;
    use DatabaseTrait;
    use PluginCommandTrait;

    /** @phpstan-var LoggerInterface|MockInterface  */
    private LoggerInterface $loggerMock;

    protected function setUp(): void
    {
        self::bootKernel();
        $kernel = self::$kernel;
        $this->assertInstanceOf(Kernel::class, $kernel);
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $this->applicationTester = new ApplicationTester($application);

        $this->loggerMock = \Mockery::mock(LoggerInterface::class);
        self::getContainer()->set('monolog.logger.plugin', $this->loggerMock);

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $this->entityManager = $entityManager;

        parent::setUp();
    }

    /**
     * @throws Exception
     */
    public function testPluginUpdate(): void
    {
        $this->assertFalse($this->columnExists('sample_plugin', 'new_column'));

        $this->loggerMock
            ->shouldReceive('error')
            ->never();

        $this->refreshPlugins();
        $this->activatePlugin('SamplePlugin', true);
        $this->updatePlugin('SamplePlugin', true);

        $this->assertFalse($this->columnExists('sample_plugin', 'new_column'));

        $repository = $this->entityManager->getRepository(Plugin::class);
        $plugin = $repository->findOneBy(['name' => 'SamplePlugin']);

        $this->assertInstanceOf(Plugin::class, $plugin);
        $plugin->setInstalledVersion('0.8.0');

        $this->entityManager->persist($plugin);
        $this->entityManager->flush();

        $this->updatePlugin('SamplePlugin', true);

        $this->assertTrue($this->columnExists('sample_plugin', 'new_column'));

        $this->refreshPlugins(true);
        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('  SamplePlugin   App\plugins\SamplePlugin\Plugin                ✓        0.9.0     0.9.0               x', $output);
        $this->assertStringContainsString('  TestPlugin     App\Tests\TestData\plugins\TestPlugin\Plugin   x        0.9.0                         x', $output);
    }

    public function testPluginUpdateWithMissingEntity(): void
    {
        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                '{pluginName} not found',
                [
                    'pluginName' => 'SamplePlugin',
                    'action' => 'UPDATE',
                ]
            );

        $this->updatePlugin('SamplePlugin');

        $this->applicationTester->assertCommandFailed();
        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('[ERROR] Plugin cannot be updated!', $output);
    }

    public function testPluginUpdateWithInvalidPlugin(): void
    {
        PluginFactory::createOne([
            'name' => 'DeletedPlugin',
            'installedVersion' => '0.8.0',
            'namespace' => '\\App\\plugins\\DeletedPlugin\\Plugin',
            'active' => true,
        ]);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                'Instance of {pluginName} not found',
                [
                    'pluginName' => 'DeletedPlugin',
                    'action' => 'UPDATE',
                ]
            );

        $this->updatePlugin('DeletedPlugin');

        $this->applicationTester->assertCommandFailed();
        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('[ERROR] Plugin cannot be updated!', $output);
    }

    public function testPluginUpdateWithMissingInstalledVersion(): void
    {
        PluginFactory::createOne([
            'name' => 'SamplePlugin',
            'namespace' => 'App\\plugins\\SamplePlugin\\Plugin',
            'active' => true,
        ]);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                'Plugin {pluginInstance} is not installed',
                [
                    'pluginInstance' => \App\plugins\SamplePlugin\Plugin::class,
                    'toVersion' => '0.9.0',
                ]
            );

        $this->updatePlugin('SamplePlugin');

        $this->applicationTester->assertCommandFailed();
        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('Plugin is not installed', $output);
    }

    public function testPluginAUpdateCausingError(): void
    {
        PluginFactory::createOne([
            'name' => 'TestPlugin',
            'installedVersion' => '0.8.0',
            'namespace' => 'App\\Tests\\TestData\\plugins\\TestPlugin\\Plugin',
            'active' => true,
        ]);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                'An error occurred at {pluginInstance}::update()',
                [
                    'pluginInstance' => \App\Tests\TestData\plugins\TestPlugin\Plugin::class,
                    'fromVersion' => '0.8.0',
                    'toVersion' => '0.9.0',
                    'message' => 'Error',
                    'exception' => \get_class(new \RuntimeException()),
                ]
            );

        $this->updatePlugin('TestPlugin');

        $this->applicationTester->assertCommandFailed();
        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('Oops', $output);
    }
}
