<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Plugin\Plugin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Plugin>
 */
class PluginRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plugin::class);
    }

    // TODO: add tests

    /** @phpstan-param Collection<int, Plugin> $pluginCollection */
    public function deleteOrphans(Collection $pluginCollection): void
    {
        $existingPlugins = $pluginCollection->map(static fn (Plugin $plugin): string => $plugin->getNamespace());

        $queryBuilder = $this->createQueryBuilder('plugin');

        $queryBuilder
            ->delete()
            ->where($queryBuilder->expr()->notIn('plugin.namespace', $existingPlugins->getValues()))
            ->getQuery()
            ->execute();
    }

    /** @phpstan-return Collection<int, Plugin> $pluginCollection */
    public function getActivePlugins(): Collection
    {
        return new ArrayCollection($this->findBy(['active' => true]));
    }

    public function getPluginEntity(string $namespace): ?Plugin
    {
        return $this->findOneBy(['namespace' => $namespace]);
    }
}
