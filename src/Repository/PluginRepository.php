<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Plugin\Plugin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    //TODO: add tests

    /** @phpstan-param Collection<int, Plugin> $pluginCollection */
    public function deleteOrphans(Collection $pluginCollection): void
    {
        $test = $pluginCollection->map(static fn (Plugin $plugin): string => $plugin->getNamespace());

        $queryBuilder = $this->createQueryBuilder('plugin');

        $queryBuilder
            ->delete()
            ->where($queryBuilder->expr()->notIn('plugin.namespace', $test->getValues()))
            ->getQuery()
            ->execute();
    }
}
