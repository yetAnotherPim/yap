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
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new Filesystem();
        $this->projectDir = sys_get_temp_dir().'/'.uniqid('plugin_manager_test_', true);

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

        self::assertSame($activePlugins, $pluginManager->getActivePlugins());
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

        self::assertTrue($pluginManager->performWorkflow(Action::ACTIVATE, 'SamplePlugin'));
    }

    public function testPerformWorkflowReturnsFalseWhenPluginInstanceNotFound(): void
    {
        $eventDispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $eventDispatcher
            ->shouldNotReceive('dispatch');

        $pluginManager = $this->createPluginManager(eventDispatcher: $eventDispatcher);

        self::assertFalse($pluginManager->performWorkflow(Action::ACTIVATE, 'UnknownPlugin'));
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

        self::assertFalse($pluginManager->performWorkflow(Action::ACTIVATE, 'SamplePlugin'));
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

        $result = $pluginManager->getPlugins();

        self::assertCount(1, $result);

        $plugin = $result->first();
        self::assertInstanceOf(Plugin::class, $plugin);
        self::assertSame('SamplePlugin', $plugin->getName());
        self::assertSame('1.0.0', $plugin->getVersion());
        self::assertSame($pluginInstance::class, $plugin->getNamespace());
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

        $result = $pluginManager->getPlugins();

        self::assertSame($existingEntity, $result->first());
        self::assertSame('SamplePlugin', $existingEntity->getName());
        self::assertSame('2.0.0', $existingEntity->getVersion());
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

        self::assertCount(0, $pluginManager->getPlugins());
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

        self::assertTrue($pluginManager->dumpPluginConfiguration());
        self::assertStringEqualsFile(
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

        self::assertFalse($pluginManager->dumpPluginConfiguration());
    }

    public function testLoadActivePluginsDoesNothingWhenDumpfileIsMissing(): void
    {
        $container = $this->createContainerMock();
        $container
            ->shouldNotReceive('setParameter');

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
            ->shouldReceive('setParameter')
            ->once()
            ->with('plugins_loaded_successfully', true);

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
        $container
            ->shouldReceive('setParameter')
            ->once()
            ->with('plugins_loaded_successfully', false);

        PluginManager::loadActivePlugins('test', $container);
    }

    private function createContainerMock(): ContainerBuilder&Mockery\MockInterface
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
        );
    }
}
