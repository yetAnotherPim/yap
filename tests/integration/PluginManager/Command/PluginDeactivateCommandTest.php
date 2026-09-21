<?php

declare(strict_types=1);

namespace App\Tests\integration\PluginManager\Command;

use App\Controller\Admin\IndexController;
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
use App\Controller\Provider\DashboardStatisticsProvider;
use App\Controller\Provider\DashboardSystemStatusProvider;
use App\Doctrine\Event\EventListener\RelatedTimestampUpdater;
use App\Entity\Plugin\Plugin;
use App\Form\Product\Fields\Composer\ProductFieldsComposer;
use App\Form\Product\Fields\Composer\Translation\ProductFieldTranslationComposer;
use App\Form\Product\Fields\Renderer\ProductFieldsRenderer;
use App\Form\Product\Fields\Renderer\Registry\RendererRegistry;
use App\Form\Product\Fields\Synchronizer\FieldSynchronizer;
use App\Form\Product\Fields\Synchronizer\Translation\FieldTranslationSynchronizer;
use App\Form\ProductType\DefaultTranslation\Synchronizer\DefaultTranslationSynchronizer;
use App\Form\Title\Translation\Renderer\TitleTranslationFieldRenderer;
use App\Form\Title\Translation\Synchronizer\TitleTranslationSynchronizer;
use App\Kernel;
use App\PluginManager\Cache\CacheClearer;
use App\PluginManager\Cache\Decorator\CacheClearerDecorator;
use App\PluginManager\Command\PluginDeactivateCommand;
use App\PluginManager\Command\PluginsListCommand;
use App\PluginManager\Event\Enum\Action;
use App\PluginManager\Event\WorkflowEvent\EventListener\PluginWorkflowEventListener;
use App\PluginManager\Event\WorkflowEvent\Exception\PluginWorkflowException;
use App\PluginManager\Event\WorkflowEvent\PluginDeactivateEvent;
use App\PluginManager\PluginManager;
use App\PluginManager\State\PluginsRefreshProvider;
use App\Repository\FieldTypeRepository;
use App\Repository\IdentifierTypeRepository;
use App\Repository\LocaleRepository;
use App\Repository\PluginRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductTypeRepository;
use App\Repository\UserRepository;
use App\Tests\helper\Trait\Integration\PluginCommandTrait;
use App\Tests\TestData\Factory\Plugin\PluginFactory;
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
use Zenstruck\Foundry\Test\Factories;

#[CoversClass(PluginDeactivateCommand::class)]
#[CoversClass(Plugin::class)]
#[CoversClass(PluginWorkflowEventListener::class)]
#[CoversClass(PluginWorkflowController::class)]
#[CoversClass(PluginManager::class)]
#[CoversClass(PluginDeactivateEvent::class)]
#[CoversClass(PluginWorkflowException::class)]
#[CoversClass(CacheClearer::class)]
#[CoversClass(CacheClearerDecorator::class)]
#[CoversClass(PluginsRefreshProvider::class)]
#[CoversClass(PluginRepository::class)]
#[CoversClass(Action::class)]
#[CoversClass(\App\plugins\Scheduler\Plugin::class)]
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
#[UsesClass(IndexController::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(RelatedTimestampUpdater::class)]
#[UsesClass(ProductFieldsComposer::class)]
#[UsesClass(RendererRegistry::class)]
#[UsesClass(FieldSynchronizer::class)]
#[UsesClass(DashboardSystemStatusProvider::class)]
#[UsesClass(DashboardStatisticsProvider::class)]
#[UsesClass(ProductFieldsRenderer::class)]
#[UsesClass(FieldTranslationSynchronizer::class)]
#[UsesClass(ProductRepository::class)]
#[UsesClass(ProductTypeRepository::class)]
#[UsesClass(FieldTypeRepository::class)]
#[UsesClass(UserRepository::class)]
#[UsesClass(TitleTranslationSynchronizer::class)]
#[UsesClass(TitleTranslationFieldRenderer::class)]
#[UsesClass(ProductFieldTranslationComposer::class)]
#[UsesClass(DefaultTranslationSynchronizer::class)]
#[RunTestsInSeparateProcesses]
/** execute tests clearing the cache in a separate environment */
#[WithEnvironmentVariable('APP_ENV', 'ct')]
class PluginDeactivateCommandTest extends KernelTestCase
{
    use Factories;
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

        parent::setUp();
    }

    public function testPluginDeactivate(): void
    {
        $dumpfilePath = __DIR__.'/../../../../var/plugin_configuration/ct_plugin_configuration';

        PluginFactory::createOne([
            'name' => 'Scheduler',
            'installedVersion' => '1.0.0',
            'namespace' => 'App\\plugins\\Scheduler\\Plugin',
            'active' => true,
        ]);

        $this->loggerMock
            ->shouldReceive('error')
            ->never();

        $this->refreshPlugins($this->kernelInstance, true);

        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('  Scheduler    App\plugins\Scheduler\Plugin                   ✓        1.0.0     1.0.0               x', $output);
        $this->assertStringContainsString('  TestPlugin   App\Tests\TestData\plugins\TestPlugin\Plugin   x        0.9.0                         x', $output);

        $this->assertFileExists($dumpfilePath);
        /** @phpstan-var string $dumpFileContent */
        $dumpFileContent = file_get_contents($dumpfilePath);

        $this->assertStringContainsString('Scheduler', $dumpFileContent);
        $this->assertStringNotContainsString('TestPlugin', $dumpFileContent);
        $this->assertStringNotContainsString('DeletedPlugin', $dumpFileContent);

        $this->deactivatePlugin('Scheduler', true);
        $this->listPlugins(true);

        $this->assertFileExists($dumpfilePath);
        /** @phpstan-var string $dumpFileContent */
        $dumpFileContent = file_get_contents($dumpfilePath);

        $this->assertStringNotContainsString('Scheduler', $dumpFileContent);
        $this->assertStringNotContainsString('TestPlugin', $dumpFileContent);

        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('  Scheduler    App\plugins\Scheduler\Plugin                   x        1.0.0     1.0.0               x', $output);
        $this->assertStringContainsString('  TestPlugin   App\Tests\TestData\plugins\TestPlugin\Plugin   x        0.9.0                         x', $output);
    }

    public function testPluginDeactivateWithMissingEntity(): void
    {
        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                '{pluginName} not found',
                [
                    'pluginName' => 'Scheduler',
                    'action' => 'DEACTIVATE',
                ]
            );

        $this->deactivatePlugin('Scheduler');

        $this->applicationTester->assertCommandFailed();
        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('[ERROR] Plugin cannot be deactivated!', $output);
    }

    public function testPluginDeactivateWithInvalidPlugin(): void
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
                    'action' => 'DEACTIVATE',
                ]
            );

        $this->deactivatePlugin('DeletedPlugin');

        $this->applicationTester->assertCommandFailed();
        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('[ERROR] Plugin cannot be deactivated!', $output);
    }

    public function testPluginDeactivateCausingError(): void
    {
        $this->refreshPlugins($this->kernelInstance);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                'An error occurred at {pluginInstance}::deactivate()',
                [
                    'pluginInstance' => \App\Tests\TestData\plugins\TestPlugin\Plugin::class,
                    'message' => 'Oops',
                    'exception' => \get_class(new \RuntimeException()),
                ]
            );

        $this->deactivatePlugin('TestPlugin');

        $this->applicationTester->assertCommandFailed();
        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('Oops', $output);
    }
}
