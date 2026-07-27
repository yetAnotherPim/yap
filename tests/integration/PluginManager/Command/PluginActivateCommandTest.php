<?php

declare(strict_types=1);

namespace App\Tests\integration\PluginManager\Command;

use App\Controller\Admin\Products\IdentifierCrudController;
use App\Controller\Admin\Products\ProductCrudController;
use App\Controller\Admin\Settings\FieldTypeCrudController;
use App\Controller\Admin\Settings\IdentifierTypeCrudController;
use App\Controller\Admin\Settings\LocaleCrudController;
use App\Controller\Admin\Settings\PluginCrudController;
use App\Controller\Admin\Settings\ProductTypeCrudController;
use App\Controller\Admin\Settings\ProductTypeFieldConfigurationCrudController;
use App\Controller\Admin\Settings\UserCrudController;
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
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\WithEnvironmentVariable;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\HttpKernel\KernelInterface;

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
#[UsesClass(ProductTypeFieldConfigurationCrudController::class)]
#[UsesClass(PluginCrudController::class)]
#[UsesClass(UserCrudController::class)]
#[RunTestsInSeparateProcesses]
/** execute tests clearing the cache in a separate environment */
#[WithEnvironmentVariable('APP_ENV', 'ct')]
class PluginActivateCommandTest extends KernelTestCase
{
    use DatabaseTrait;
    use PluginCommandTrait;

    /** @phpstan-var LoggerInterface|MockInterface  */
    private LoggerInterface $loggerMock;
    private KernelInterface $kernelInstance;

    protected function setUp(): void
    {
        self::bootKernel(['debug' => true]);
        $kernel = self::$kernel;

        $this->assertInstanceOf(Kernel::class, $kernel);
        $this->kernelInstance = $kernel;

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
        $dumpfilePath = __DIR__.'/../../../../var/plugin_configuration/ct_plugin_configuration';
        file_put_contents($dumpfilePath, '');
        $this->assertFalse($this->tableExists('sample_plugin'));

        $this->loggerMock
            ->shouldReceive('error')
            ->never();

        $this->refreshPlugins($this->kernelInstance);
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

        $this->refreshPlugins($this->kernelInstance);
        $this->activatePlugin('TestPlugin');

        $this->applicationTester->assertCommandFailed();
        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('Oops', $output);
    }
}
