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
use App\Form\Product\Columns\Composer\ProductColumnsComposer;
use App\Form\Product\Fields\Composer\ProductFieldsComposer;
use App\Form\Product\Fields\Composer\Translation\ProductFieldTranslationComposer;
use App\Form\Product\Fields\Renderer\ProductFieldsRenderer;
use App\Form\Product\Fields\Renderer\Registry\RendererRegistry;
use App\Form\Product\Fields\Synchronizer\FieldSynchronizer;
use App\Form\Product\Fields\Synchronizer\Translation\FieldTranslationSynchronizer;
use App\Form\Product\Filter\Composer\ProductFilterComposer;
use App\Form\Product\Filter\Element\Field\Registry\FieldFilterHandlerRegistry;
use App\Form\Product\Identifier\IdentifierManager;
use App\Form\ProductType\DefaultTranslation\Synchronizer\DefaultTranslationSynchronizer;
use App\Form\Title\Translation\Renderer\TitleTranslationFieldRenderer;
use App\Form\Title\Translation\Synchronizer\TitleTranslationSynchronizer;
use App\Kernel;
use App\PluginManager\Cache\CacheClearer;
use App\PluginManager\Cache\Decorator\CacheClearerDecorator;
use App\PluginManager\Command\PluginActivateCommand;
use App\PluginManager\Command\PluginsListCommand;
use App\PluginManager\Command\PluginUpdateCommand;
use App\PluginManager\Event\Enum\Action;
use App\PluginManager\Event\WorkflowEvent\EventListener\PluginWorkflowEventListener;
use App\PluginManager\Event\WorkflowEvent\Exception\PluginWorkflowException;
use App\PluginManager\Event\WorkflowEvent\PluginActivateEvent;
use App\PluginManager\Event\WorkflowEvent\PluginUpdateEvent;
use App\PluginManager\PluginManager;
use App\PluginManager\State\PluginsRefreshProvider;
use App\Repository\FieldTypeRepository;
use App\Repository\IdentifierTypeRepository;
use App\Repository\LocaleRepository;
use App\Repository\PluginRepository;
use App\Repository\ProductColumnConfigurationRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductTypeRepository;
use App\Repository\UserRepository;
use App\Tests\helper\Trait\DatabaseTrait;
use App\Tests\helper\Trait\Integration\PluginCommandTrait;
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
use Zenstruck\Foundry\Test\Factories;

#[CoversClass(PluginUpdateCommand::class)]
#[CoversClass(Plugin::class)]
#[CoversClass(PluginWorkflowEventListener::class)]
#[CoversClass(PluginWorkflowController::class)]
#[CoversClass(PluginManager::class)]
#[CoversClass(PluginUpdateEvent::class)]
#[CoversClass(PluginWorkflowException::class)]
#[CoversClass(CacheClearer::class)]
#[CoversClass(CacheClearerDecorator::class)]
#[CoversClass(PluginsRefreshProvider::class)]
#[CoversClass(PluginRepository::class)]
#[CoversClass(Action::class)]
#[CoversClass(\App\plugins\Scheduler\Plugin::class)]
#[UsesClass(PluginsListCommand::class)]
#[UsesClass(PluginActivateCommand::class)]
#[UsesClass(PluginActivateEvent::class)]
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
#[UsesClass(ProductColumnsComposer::class)]
#[UsesClass(ProductFilterComposer::class)]
#[UsesClass(IdentifierManager::class)]
#[UsesClass(FieldFilterHandlerRegistry::class)]
#[UsesClass(ProductColumnConfigurationRepository::class)]
#[RunTestsInSeparateProcesses]
/** execute tests clearing the cache in a separate environment */
#[WithEnvironmentVariable('APP_ENV', 'ct')]
class PluginUpdateCommandTest extends KernelTestCase
{
    use Factories;
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
    #[WithEnvironmentVariable('APP_ENV', 'ct')]
    public function testPluginUpdate(): void
    {
        $this->loggerMock
            ->shouldReceive('error')
            ->never();

        $this->refreshPlugins($this->kernelInstance);
        $this->activatePlugin('Scheduler', true);
        $this->updatePlugin('Scheduler', true);

        $repository = $this->entityManager->getRepository(Plugin::class);
        $plugin = $repository->findOneBy(['name' => 'Scheduler']);

        $this->assertInstanceOf(Plugin::class, $plugin);
        $plugin->setInstalledVersion('0.9.0');

        $this->entityManager->persist($plugin);
        $this->entityManager->flush();

        $this->updatePlugin('Scheduler', true);

        $this->assertTrue($this->columnExists('yap_scheduler', 'last_run'));

        $this->refreshPlugins($this->kernelInstance, true);
        $output = $this->applicationTester->getDisplay();

        $this->assertStringContainsString('  Scheduler    App\plugins\Scheduler\Plugin                   ✓        1.0.0     1.0.0               x', $output);
        $this->assertStringContainsString('  TestPlugin   App\Tests\TestData\plugins\TestPlugin\Plugin   x        0.9.0                         x', $output);
    }

    public function testPluginUpdateWithMissingEntity(): void
    {
        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                '{pluginName} not found',
                [
                    'pluginName' => 'Scheduler',
                    'action' => 'UPDATE',
                ]
            );

        $this->updatePlugin('Scheduler');

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
            'name' => 'Scheduler',
            'namespace' => 'App\\plugins\\Scheduler\\Plugin',
            'active' => true,
        ]);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                'Plugin {pluginInstance} is not installed',
                [
                    'pluginInstance' => \App\plugins\Scheduler\Plugin::class,
                    'toVersion' => '1.0.0',
                ]
            );

        $this->updatePlugin('Scheduler');

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
