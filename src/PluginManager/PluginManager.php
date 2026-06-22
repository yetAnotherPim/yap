<?php

declare(strict_types=1);

namespace App\PluginManager;

use App\Entity\Plugin\Plugin;
use App\PluginManager\Plugin\PluginInterface;
use App\Repository\PluginRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class PluginManager
{
    /** @phpstan-var Collection<int, Plugin> $plugins */
    private Collection $plugins;

    /** @phpstan-param iterable<int, Plugin> $plugins */
    public function __construct(
        #[AutowireIterator('plugin')]
        iterable $plugins,
        private PluginRepository $pluginRepository,
        private EntityManagerInterface $entityManager,
    ) {
        $plugins = $plugins instanceof \Traversable ? iterator_to_array($plugins) : $plugins;

        $this->plugins = new ArrayCollection($plugins);
    }

    // TODO: test activation/deactivation with loading of services
    // TODO: test update
    // TODO: test refresh with updating entities

    // TODO: getActivePlugins

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

            $pluginEntity = $this->getPluginEntity(\get_class($plugin));

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
        // TODO: check if plugin still exists

        // TODO: autoload plugin services
        // TODO: add services.yaml to autoload config
        // TODO: lod twig folder

        return new ArrayCollection();
    }

    private function getPluginEntity(string $namespace): Plugin
    {
        return $this->pluginRepository->findOneBy(['namespace' => $namespace]) ?? new Plugin()->setNamespace($namespace);
    }
}
