<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product\Field\Type\FieldType;
use App\Entity\User\Configuration\ProductColumnConfiguration;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\QueryException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductColumnConfiguration>
 */
class ProductColumnConfigurationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductColumnConfiguration::class);
    }

    /**
     * @return Collection<string, ProductColumnConfiguration>
     *
     * @throws QueryException
     */
    public function getColumnsForUser(User $user): Collection
    {
        $result = $this->createQueryBuilder('configuration')
            ->indexBy('configuration', 'configuration.fieldType')
            ->andWhere('configuration.user = :user')
            ->setParameter('user', $user)
            ->orderBy('configuration.position', 'ASC')
            ->getQuery()
            ->getResult();

        return new ArrayCollection($result);
    }

    /**
     * @param Collection<string, ProductColumnConfiguration> $configurations
     *
     * @return Collection<int, FieldType>
     */
    public function getNotConfiguredFieldTypes(
        Collection $configurations,
    ): Collection {
        $fieldTypeRepository = $this->getEntityManager()
            ->getRepository(FieldType::class);

        $queryBuilder = $fieldTypeRepository->createQueryBuilder('fieldType');

        if (!$configurations->isEmpty()) {
            $queryBuilder
                ->andWhere('fieldType.id NOT IN (:fieldTypeIds)')
                ->setParameter('fieldTypeIds', $configurations->getKeys());
        }

        return new ArrayCollection(
            $queryBuilder
                ->getQuery()
                ->getResult(),
        );
    }
}
