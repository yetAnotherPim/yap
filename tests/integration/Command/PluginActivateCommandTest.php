<?php

declare(strict_types=1);

namespace App\Tests\integration\Command;

use App\Controller\Admin\FieldTypeCrudController;
use App\Controller\Admin\IdentifierCrudController;
use App\Controller\Admin\IdentifierTypeCrudController;
use App\Controller\Admin\LocaleCrudController;
use App\Controller\Admin\ProductCrudController;
use App\Controller\Admin\ProductTypeCrudController;
use App\Controller\Api\PluginWorkflowController;
use App\Entity\Plugin\Plugin;
use App\Kernel;
use App\PluginManager\Cache\CacheClearer;
use App\PluginManager\Cache\Decorator\CacheClearerDecorator;
use App\PluginManager\Command\PluginActivateCommand;
use App\PluginManager\Command\PluginsListCommand;
use App\PluginManager\DependencyInjection\CompilerPass\PluginLoader;
use App\PluginManager\Event\Enum\Action;
use App\PluginManager\Event\WorkflowEvent\EventListener\PluginWorkflowEventListener;
use App\PluginManager\Event\WorkflowEvent\Exception\PluginWorkflowException;
use App\PluginManager\Event\WorkflowEvent\PluginActivateEvent;
use App\PluginManager\PluginManager;
use App\PluginManager\State\PluginsRefreshProvider;
use App\Repository\IdentifierTypeRepository;
use App\Repository\PluginRepository;
use App\Tests\helper\Trait\DatabaseTrait;
use App\Tests\helper\Trait\PluginCommandTrait;
use App\Tests\TestData\Factory\Plugin\PluginFactory;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;
use Zenstruck\Foundry\Test\Factories;

#[CoversClass(PluginActivateCommand::class)]
#[CoversClass(Plugin::class)]
#[CoversClass(PluginWorkflowEventListener::class)]
#[CoversClass(PluginWorkflowController::class)]
#[CoversClass(PluginManager::class)]
#[CoversClass(PluginLoader::class)]
#[CoversClass(PluginActivateEvent::class)]
#[CoversClass(PluginWorkflowException::class)]
#[CoversClass(CacheClearer::class)]
#[CoversClass(CacheClearerDecorator::class)]
#[CoversClass(PluginsRefreshProvider::class)]
#[CoversClass(PluginRepository::class)]
#[CoversClass(Action::class)]
#[CoversClass(Kernel::class)]
#[CoversClass(\App\plugins\SamplePlugin\Plugin::class)]
#[UsesClass(PluginsListCommand::class)]
// Deleting the cache is causing instantiation of following classes
#[UsesClass(FieldTypeCrudController::class)]
#[UsesClass(IdentifierCrudController::class)]
#[UsesClass(IdentifierTypeCrudController::class)]
#[UsesClass(IdentifierTypeRepository::class)]
#[UsesClass(LocaleCrudController::class)]
#[UsesClass(ProductTypeCrudController::class)]
#[UsesClass(ProductCrudController::class)]
class PluginActivateCommandTest extends KernelTestCase
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
    public function testPluginActivate(): void
    {
        $dumpfilePath = __DIR__.'/../../../var/plugin_configuration/test_plugin_configuration';
        file_put_contents($dumpfilePath, '');
        $this->assertFalse($this->tableExists('sample_plugin'));

        $this->loggerMock
            ->shouldReceive('error')
            ->never();

        $this->refreshPlugins();
        $this->activatePlugin('SamplePlugin', true);

        $this->assertTrue($this->tableExists('sample_plugin'));

        $this->listPlugins(true);

        $this->assertFileExists($dumpfilePath);
        /** @phpstan-var string $dumpFileContent */
        $dumpFileContent = file_get_contents($dumpfilePath);

        $this->assertStringContainsString('SamplePlugin', $dumpFileContent);
        $this->assertStringNotContainsString('TestPlugin', $dumpFileContent);

        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('  SamplePlugin   App\plugins\SamplePlugin\Plugin                ✓        0.9.0     0.9.0               x', $output);
        $this->assertStringContainsString('  TestPlugin     App\Tests\TestData\plugins\TestPlugin\Plugin   x        0.9.0                         x', $output);
    }

    public function testPluginActivateWithMissingEntity(): void
    {
        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                '{pluginName} not found',
                [
                    'pluginName' => 'SamplePlugin',
                    'action' => 'ACTIVATE',
                ]
            );

        $this->activatePlugin('SamplePlugin');

        $this->applicationTester->assertCommandFailed();
        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('[ERROR] Plugin cannot be activated!', $output);
    }

    public function testPluginActivateWithInvalidPlugin(): void
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
                    'action' => 'ACTIVATE',
                ]
            );

        $this->activatePlugin('DeletedPlugin');

        $this->applicationTester->assertCommandFailed();
        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('[ERROR] Plugin cannot be activated!', $output);
    }

    public function testPluginActivateCausingError(): void
    {
        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                'An error occurred at {pluginInstance}::activate()',
                [
                    'pluginInstance' => \App\Tests\TestData\plugins\TestPlugin\Plugin::class,
                    'message' => 'Oops',
                    'exception' => \get_class(new \RuntimeException()),
                ]
            );

        $this->refreshPlugins();
        $this->activatePlugin('TestPlugin');

        $this->applicationTester->assertCommandFailed();
        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('Oops', $output);
    }
}
