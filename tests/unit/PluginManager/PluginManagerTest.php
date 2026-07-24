<?php

declare(strict_types=1);

namespace App\Tests\unit\PluginManager;

use App\Entity\Plugin\Plugin;
use App\PluginManager\Event\Enum\Action;
use App\PluginManager\Event\WorkflowEvent\Exception\PluginWorkflowException;
use App\PluginManager\Event\WorkflowEvent\PluginActivateEvent;
use App\PluginManager\Plugin\PluginInterface;
use App\PluginManager\PluginManager;
use App\Repository\PluginRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(PluginManager::class)]
#[UsesClass(Plugin::class)]
#[UsesClass(Action::class)]
#[UsesClass(PluginActivateEvent::class)]
class PluginManagerTest extends MockeryTestCase
{
    private Filesystem $filesystem;

    private string $projectDir;

    /** @phpstan-var LoggerInterface|MockInterface  */
    private LoggerInterface $loggerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();
        $this->projectDir = sys_get_temp_dir().'/'.uniqid('plugin_manager_test_', true);

        $this->loggerMock = \Mockery::mock(LoggerInterface::class);

        $this->filesystem->mkdir($this->projectDir);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->projectDir);

        parent::tearDown();
    }

    public function testGetActivePlugins(): void
    {
        $activePlugins = new ArrayCollection([new Plugin()]);

        $pluginRepository = \Mockery::mock(PluginRepository::class);
        $pluginRepository
            ->shouldReceive('getActivePlugins')
            ->once()
            ->andReturn($activePlugins);

        $pluginManager = $this->createPluginManager(pluginRepository: $pluginRepository);

        $this->loggerMock
            ->shouldReceive('error')
            ->never();

        $this->assertSame($activePlugins, $pluginManager->getActivePlugins());
    }

    public function testPerformWorkflowDispatchesEventAndReturnsTrue(): void
    {
        $pluginInstance = \Mockery::mock(PluginInterface::class);
        $pluginInstance
            ->shouldReceive('getName')
            ->andReturn('SamplePlugin');

        $pluginEntity = new Plugin();

        $pluginRepository = \Mockery::mock(PluginRepository::class);
        $pluginRepository
            ->shouldReceive('getPluginEntity')
            ->once()
            ->with($pluginInstance::class)
            ->andReturn($pluginEntity);

        $eventDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(PluginActivateEvent::class))
            ->andReturnUsing(static fn (object $event): object => $event);

        $pluginManager = $this->createPluginManager(
            plugins: [$pluginInstance],
            pluginRepository: $pluginRepository,
            eventDispatcher: $eventDispatcher,
        );

        $this->loggerMock
            ->shouldReceive('error')
            ->never();

        $this->assertTrue($pluginManager->performWorkflow(Action::ACTIVATE, 'SamplePlugin'));
    }

    public function testPerformWorkflowReturnsFalseWhenPluginInstanceNotFound(): void
    {
        $eventDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher
            ->shouldNotReceive('dispatch');

        $pluginManager = $this->createPluginManager(eventDispatcher: $eventDispatcher);

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                'Instance of {pluginName} not found',
                [
                    'pluginName' => 'UnknownPlugin',
                    'action' => 'ACTIVATE',
                ]
            );

        $this->assertFalse($pluginManager->performWorkflow(Action::ACTIVATE, 'UnknownPlugin'));
    }

    /**
     * @throws PluginWorkflowException
     */
    public function testPerformWorkflowReturnsFalseWhenPluginEntityIsMissing(): void
    {
        $pluginInstance = \Mockery::mock(PluginInterface::class);
        $pluginInstance
            ->shouldReceive('getName')
            ->andReturn('SamplePlugin');

        $pluginRepository = \Mockery::mock(PluginRepository::class);
        $pluginRepository
            ->shouldReceive('getPluginEntity')
            ->once()
            ->with($pluginInstance::class)
            ->andReturnNull();

        $eventDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher
            ->shouldNotReceive('dispatch');

        $pluginManager = $this->createPluginManager(
            plugins: [$pluginInstance],
            pluginRepository: $pluginRepository,
            eventDispatcher: $eventDispatcher,
        );

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

        $this->assertFalse($pluginManager->performWorkflow(Action::ACTIVATE, 'SamplePlugin'));
    }

    public function testGetPluginsCreatesEntityForUnknownPlugin(): void
    {
        $pluginInstance = \Mockery::mock(PluginInterface::class);
        $pluginInstance
            ->shouldReceive('getName')
            ->andReturn('SamplePlugin');
        $pluginInstance
            ->shouldReceive('getVersion')
            ->andReturn('1.0.0');

        $pluginRepository = \Mockery::mock(PluginRepository::class);
        $pluginRepository
            ->shouldReceive('getPluginEntity')
            ->once()
            ->with($pluginInstance::class)
            ->andReturnNull();
        $pluginRepository
            ->shouldReceive('deleteOrphans')
            ->once()
            ->with(\Mockery::type(Collection::class));

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager
            ->shouldReceive('persist')
            ->once()
            ->with(\Mockery::type(Plugin::class));
        $entityManager
            ->shouldReceive('flush')
            ->once();

        $pluginManager = $this->createPluginManager(
            plugins: [$pluginInstance],
            pluginRepository: $pluginRepository,
            entityManager: $entityManager,
        );

        $this->loggerMock
            ->shouldReceive('error')
            ->never();

        $result = $pluginManager->getPlugins();

        $this->assertCount(1, $result);

        $plugin = $result->first();
        $this->assertInstanceOf(Plugin::class, $plugin);
        $this->assertSame('SamplePlugin', $plugin->getName());
        $this->assertSame('1.0.0', $plugin->getVersion());
        $this->assertSame($pluginInstance::class, $plugin->getNamespace());
    }

    public function testGetPluginsReusesExistingEntity(): void
    {
        $pluginInstance = \Mockery::mock(PluginInterface::class);
        $pluginInstance
            ->shouldReceive('getName')
            ->andReturn('SamplePlugin');
        $pluginInstance
            ->shouldReceive('getVersion')
            ->andReturn('2.0.0');

        $existingEntity = new Plugin()->setNamespace($pluginInstance::class);

        $pluginRepository = \Mockery::mock(PluginRepository::class);
        $pluginRepository
            ->shouldReceive('getPluginEntity')
            ->once()
            ->with($pluginInstance::class)
            ->andReturn($existingEntity);
        $pluginRepository
            ->shouldReceive('deleteOrphans')
            ->once();

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager
            ->shouldReceive('persist')
            ->once()
            ->with($existingEntity);
        $entityManager
            ->shouldReceive('flush')
            ->once();

        $pluginManager = $this->createPluginManager(
            plugins: [$pluginInstance],
            pluginRepository: $pluginRepository,
            entityManager: $entityManager,
        );

        $this->loggerMock
            ->shouldReceive('error')
            ->never();

        $result = $pluginManager->getPlugins();

        $this->assertSame($existingEntity, $result->first());
        $this->assertSame('SamplePlugin', $existingEntity->getName());
        $this->assertSame('2.0.0', $existingEntity->getVersion());
    }

    public function testGetPluginsSkipsNonPluginInstances(): void
    {
        $pluginRepository = \Mockery::mock(PluginRepository::class);
        $pluginRepository
            ->shouldReceive('getPluginEntity')
            ->never();
        $pluginRepository
            ->shouldReceive('deleteOrphans')
            ->once();

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager
            ->shouldReceive('persist')
            ->never();
        $entityManager
            ->shouldReceive('flush')
            ->once();

        $pluginManager = $this->createPluginManager(
            /* @phpstan-ignore-next-line */
            plugins: [new \stdClass()],
            pluginRepository: $pluginRepository,
            entityManager: $entityManager,
        );

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                '{pluginInstance} is invalid',
                [
                    'pluginInstance' => \get_class(new \stdClass()),
                ]
            );

        $this->assertCount(0, $pluginManager->getPlugins());
    }

    public function testDumpPluginConfigurationWritesActivePluginsWithServiceDefinition(): void
    {
        $activePlugins = new ArrayCollection([
            new Plugin()->setName('SamplePlugin'),
            new Plugin()->setName('NonExistentPlugin'),
        ]);

        $pluginRepository = \Mockery::mock(PluginRepository::class);
        $pluginRepository
            ->shouldReceive('getActivePlugins')
            ->once()
            ->andReturn($activePlugins);

        $pluginManager = $this->createPluginManager(
            pluginRepository: $pluginRepository,
            appEnv: 'test',
            projectDir: $this->projectDir,
            pluginConfigurationFolder: '/',
        );

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                'Service definition file for {pluginName} not found',
                [
                    'pluginName' => 'NonExistentPlugin',
                ]
            );

        $this->assertTrue($pluginManager->dumpPluginConfiguration());
        $this->assertStringEqualsFile(
            $this->projectDir.'/test_plugin_configuration',
            'SamplePlugin'
        );
    }

    public function testDumpPluginConfigurationReturnsFalseWhenNoServiceDefinitionMatches(): void
    {
        $activePlugins = new ArrayCollection([
            new Plugin()->setName('NonExistentPlugin'),
        ]);

        $pluginRepository = \Mockery::mock(PluginRepository::class);
        $pluginRepository
            ->shouldReceive('getActivePlugins')
            ->once()
            ->andReturn($activePlugins);

        $pluginManager = $this->createPluginManager(
            pluginRepository: $pluginRepository,
            appEnv: 'test',
            projectDir: $this->projectDir,
            pluginConfigurationFolder: '/',
        );

        $this->loggerMock
            ->shouldReceive('error')
            ->once()
            ->with(
                'Service definition file for {pluginName} not found',
                [
                    'pluginName' => 'NonExistentPlugin',
                ]
            );

        $this->assertFalse($pluginManager->dumpPluginConfiguration());
    }

    public function testLoadActivePluginsDoesNothingWhenDumpfileIsMissing(): void
    {
        $container = $this->createContainerMock();
        $container
            ->shouldNotReceive('setParameter');

        $container
            ->shouldReceive('getDefinition')
            ->never()
            ->with('twig.loader.native_filesystem');

        $this->loggerMock
            ->shouldReceive('error')
            ->never();

        PluginManager::loadActivePlugins('test', $container);
    }

    public function testLoadActivePluginsFlagsSuccessWhenServiceDefinitionIsMissing(): void
    {
        /* a configured plugin without a services.yaml is skipped, the run still counts as successful */
        $this->filesystem->dumpFile(
            $this->projectDir.'/test_plugin_configuration',
            'NonExistentPlugin'
        );

        $container = $this->createContainerMock();

        $container
            ->shouldReceive('getDefinition')
            ->once()
            ->with('twig.loader.native_filesystem')
            ->andReturn(\Mockery::mock(Definition::class));

        $container
            ->shouldReceive('setParameter')
            ->once()
            ->with('plugins_loaded_successfully', true);

        $this->loggerMock
            ->shouldReceive('error')
            ->never();

        PluginManager::loadActivePlugins('test', $container);
    }

    public function testLoadActivePlugins(): void
    {
        /* a configured plugin without a services.yaml is skipped, the run still counts as successful */
        $this->filesystem->dumpFile(
            $this->projectDir.'/test_plugin_configuration',
            'SamplePlugin'
        );

        $container = $this->createContainerMock();
        $definitionMock = \Mockery::mock(Definition::class);

        $definitionMock
            ->shouldReceive('addMethodCall')
            ->once()
            ->with('addPath', \Mockery::type('array'))
            ->andReturn($definitionMock);

        $container
            ->shouldReceive('getDefinition')
            ->once()
            ->with('twig.loader.native_filesystem')
            ->andReturn($definitionMock);

        $container
            ->shouldReceive('fileExists')
            ->once()
            ->with(\Mockery::type('string'))
            ->andReturn(true);

        $parameterBagMock = \Mockery::mock(ParameterBagInterface::class);

        $container
            ->shouldReceive('getParameterBag')
            ->once()
            ->andReturn($parameterBagMock);

        $parameterBagMock
            ->shouldReceive('resolveValue')
            ->with([0 => './Plugin.php'])
            ->once()
            ->andReturn('./Plugin.php');

        $parameterBagMock
            ->shouldReceive('unescapeValue')
            ->with('./Plugin.php')
            ->once()
            ->andReturn('./Plugin.php');

        $parameterBagMock
            ->shouldReceive('resolveValue')
            ->with('./')
            ->once()
            ->andReturn('./');

        $parameterBagMock
            ->shouldReceive('unescapeValue')
            ->with('./')
            ->once()
            ->andReturn('./');

        $reflectionClassMock = \Mockery::mock(\ReflectionClass::class);

        $container
            ->shouldReceive('getReflectionClass')
            ->andReturn($reflectionClassMock);

        $reflectionClassMock
            ->shouldReceive('isTrait')
            ->andReturn(false);

        $reflectionClassMock
            ->shouldReceive('isAbstract')
            ->andReturn(false);

        $reflectionClassMock
            ->shouldReceive('isInterface')
            ->andReturn(false);

        $reflectionClassMock
            ->shouldReceive('getAttributes')
             ->andReturn([]);

        $container
            ->shouldReceive('addResource')
            ->andReturn($container);

        $container
            ->shouldReceive('removeBindings')
            ->andReturn($container);

        $container
            ->shouldReceive('setDefinition')
            ->andReturn(\Mockery::mock(Definition::class));

        $container
            ->shouldReceive('setParameter')
            ->once()
            ->with('plugins_loaded_successfully', true);

        $this->loggerMock
            ->shouldReceive('error')
            ->never();

        PluginManager::loadActivePlugins('test', $container);
    }

    public function testLoadActivePluginsWithExceptionOnLoad(): void
    {
        /* a configured plugin without a services.yaml is skipped, the run still counts as successful */
        $this->filesystem->dumpFile(
            $this->projectDir.'/test_plugin_configuration',
            'SamplePlugin'
        );

        $container = $this->createContainerMock();

        $container
            ->shouldReceive('getDefinition')
            ->once()
            ->with('twig.loader.native_filesystem')
            ->andReturn(\Mockery::mock(Definition::class));

        $container
            ->shouldReceive('setParameter')
            ->once()
            ->with('plugins_loaded_successfully', false);

        $this->loggerMock
            ->shouldReceive('error')
            ->never();

        PluginManager::loadActivePlugins('test', $container);
    }

    public function testLoadActivePluginsWhenTwigLoaderIsNotAvailable(): void
    {
        /* a configured plugin without a services.yaml is skipped, the run still counts as successful */
        $this->filesystem->dumpFile(
            $this->projectDir.'/test_plugin_configuration',
            'SamplePlugin'
        );

        $container = $this->createContainerMock();
        $definitionMock = \Mockery::mock(Definition::class);

        $definitionMock
            ->shouldReceive('addMethodCall')
            ->never()
            ->andReturn($definitionMock);

        $container
            ->shouldReceive('getDefinition')
            ->once()
            ->with('twig.loader.native_filesystem')
            ->andThrow(new ServiceNotFoundException('twig.loader.native_filesystem'));

        $container
            ->shouldReceive('fileExists')
            ->once()
            ->with(\Mockery::type('string'))
            ->andReturn(true);

        $parameterBagMock = \Mockery::mock(ParameterBagInterface::class);

        $container
            ->shouldReceive('getParameterBag')
            ->once()
            ->andReturn($parameterBagMock);

        $parameterBagMock
            ->shouldReceive('resolveValue')
            ->with([0 => './Plugin.php'])
            ->once()
            ->andReturn('./Plugin.php');

        $parameterBagMock
            ->shouldReceive('unescapeValue')
            ->with('./Plugin.php')
            ->once()
            ->andReturn('./Plugin.php');

        $parameterBagMock
            ->shouldReceive('resolveValue')
            ->with('./')
            ->once()
            ->andReturn('./');

        $parameterBagMock
            ->shouldReceive('unescapeValue')
            ->with('./')
            ->once()
            ->andReturn('./');

        $reflectionClassMock = \Mockery::mock(\ReflectionClass::class);

        $container
            ->shouldReceive('getReflectionClass')
            ->andReturn($reflectionClassMock);

        $reflectionClassMock
            ->shouldReceive('isTrait')
            ->andReturn(false);

        $reflectionClassMock
            ->shouldReceive('isAbstract')
            ->andReturn(false);

        $reflectionClassMock
            ->shouldReceive('isInterface')
            ->andReturn(false);

        $reflectionClassMock
            ->shouldReceive('getAttributes')
             ->andReturn([]);

        $container
            ->shouldReceive('addResource')
            ->andReturn($container);

        $container
            ->shouldReceive('removeBindings')
            ->andReturn($container);

        $container
            ->shouldReceive('setDefinition')
            ->andReturn(\Mockery::mock(Definition::class));

        $container
            ->shouldReceive('setParameter')
            ->once()
            ->with('plugins_loaded_successfully', true);

        $this->loggerMock
            ->shouldReceive('error')
            ->never();

        PluginManager::loadActivePlugins('test', $container);
    }

    private function createContainerMock(): ContainerBuilder&MockInterface
    {
        $container = \Mockery::mock(ContainerBuilder::class);
        $container
            ->shouldReceive('getParameter')
            ->with('kernel.project_dir')
            ->andReturn($this->projectDir);
        $container
            ->shouldReceive('getParameter')
            ->with('plugin_configuration_folder')
            ->andReturn('/');

        return $container;
    }

    /**
     * @param array<int, PluginInterface> $plugins
     */
    private function createPluginManager(
        array $plugins = [],
        ?PluginRepository $pluginRepository = null,
        ?EntityManagerInterface $entityManager = null,
        string $appEnv = 'test',
        string $projectDir = '',
        string $pluginConfigurationFolder = '',
        ?EventDispatcherInterface $eventDispatcher = null,
    ): PluginManager {
        return new PluginManager(
            $plugins,
            $pluginRepository ?? \Mockery::mock(PluginRepository::class),
            $entityManager ?? \Mockery::mock(EntityManagerInterface::class),
            $appEnv,
            $projectDir,
            $pluginConfigurationFolder,
            $eventDispatcher ?? \Mockery::mock(EventDispatcherInterface::class),
            $this->loggerMock
        );
    }
}
