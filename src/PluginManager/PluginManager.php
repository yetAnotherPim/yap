<?php

declare(strict_types=1);

namespace App\PluginManager;

use App\Entity\Plugin\Plugin;
use App\PluginManager\Event\Enum\Action;
use App\PluginManager\Event\WorkflowEvent\Exception\PluginWorkflowException;
use App\PluginManager\Plugin\PluginInterface;
use App\Repository\PluginRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PluginManager
{
    private const string SERVICE_DEFINITION_FILE = 'services.yaml';
    private const string PLUGIN_FOLDER = __DIR__.'/../plugins/';

    /** @phpstan-var Collection<int, PluginInterface> $plugins */
    private Collection $plugins;

    /** @phpstan-param iterable<int, PluginInterface> $plugins */
    public function __construct(
        #[AutowireIterator('plugin')]
        iterable $plugins,
        private readonly PluginRepository $pluginRepository,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(param: 'kernel.environment')]
        private readonly string $appEnv,
        #[Autowire(param: 'kernel.project_dir')]
        private readonly string $projectDir,
        #[Autowire(param: 'plugin_configuration_folder')]
        private readonly string $pluginConfigurationFolder,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        $plugins = $plugins instanceof \Traversable ? iterator_to_array($plugins) : $plugins;

        $this->plugins = new ArrayCollection($plugins);
    }

    /**
     * @throws PluginWorkflowException
     */
    public function performWorkflow(Action $action, string $name): bool
    {
        $pluginInstance = $this->plugins->findFirst(static fn (int $key, PluginInterface $plugin): bool => $plugin->getName() === $name);

        if (!$pluginInstance instanceof PluginInterface) {
            return false;
        }

        $pluginEntity = $this->pluginRepository->getPluginEntity(\get_class($pluginInstance));

        if (null === $pluginEntity) {
            return false;
        }

        $this->eventDispatcher->dispatch(new $action->value($pluginInstance, $pluginEntity));

        return true;
    }

    // TODO: test commands as api
    // TODO: test update
        //api
        //command
    // TDOD: add integration tests for whole pluginmanager

    // TODO: add unit tests

    /**
     * @return Collection<int, Plugin>
     */
    public function getPlugins(): Collection
    {
        /** @phpstan-var Collection<int, Plugin> $pluginCollection */
        $pluginCollection = new ArrayCollection();

        foreach ($this->plugins as $plugin) {
            if (!$plugin instanceof PluginInterface) {
                // TODO: log plugin log
                continue;
            }

            $pluginEntity = $this->pluginRepository->getPluginEntity(\get_class($plugin)) ?? new Plugin()->setNamespace(\get_class($plugin));

            $pluginEntity
                ->setName($plugin->getName())
                ->setVersion($plugin->getVersion());

            $this->entityManager
                ->persist($pluginEntity);

            $pluginCollection->add($pluginEntity);
        }

        $this->pluginRepository->deleteOrphans($pluginCollection);

        $this->entityManager
            ->flush();

        return $pluginCollection;
    }

    /**
     * @return Collection<int, Plugin>
     */
    public function getActivePlugins(): Collection
    {
        return $this->pluginRepository->getActivePlugins();
    }

    public function dumpPluginConfiguration(): bool
    {
        $activePlugins = [];

        foreach ($this->getActivePlugins() as $plugin) {
            $serviceDefinitionFile = $plugin->getName().'/'.self::SERVICE_DEFINITION_FILE;

            if (false === file_exists(self::PLUGIN_FOLDER.$serviceDefinitionFile)) {
                continue;
            }

            $activePlugins[] = $plugin->getName();
        }

        return (bool) file_put_contents(
            $this->projectDir.$this->pluginConfigurationFolder.$this->appEnv.'_plugin_configuration',
            implode(PHP_EOL, $activePlugins)
        );
    }

    public static function loadActivePlugins(string $appEnv, ContainerBuilder $container): void
    {
        /** @var string $projectDir */
        $projectDir = $container->getParameter('kernel.project_dir');
        /** @var string $pluginConfigurationFolder */
        $pluginConfigurationFolder = $container->getParameter('plugin_configuration_folder');
        $successfullyLoaded = true;

        $dumpedConfigurationFile = $projectDir.$pluginConfigurationFolder.$appEnv.'_plugin_configuration';

        if (false === file_exists($dumpedConfigurationFile)) {
            return;
        }

        $activePlugins = file($dumpedConfigurationFile);

        if (false === $activePlugins) {
            return;
        }

        foreach ($activePlugins as $pluginName) {
            $serviceDefinitionFile = self::PLUGIN_FOLDER.trim($pluginName).'/'.self::SERVICE_DEFINITION_FILE;

            if (false === file_exists($serviceDefinitionFile)) {
                continue;
            }

            $loader = new YamlFileLoader(
                $container,
                new FileLocator(self::PLUGIN_FOLDER.'/'.trim($pluginName))
            );

            try {
                $loader->load(self::SERVICE_DEFINITION_FILE);
            } catch (\Exception $e) {
                $successfullyLoaded = false;
            }
        }

        $container->setParameter('plugins_loaded_successfully', $successfullyLoaded);
    }
}
