<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product\Field\Type\Enum\InputType;
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

        $queryBuilder = $fieldTypeRepository->createQueryBuilder('fieldType')
            ->andWhere('fieldType.inputType != :inputType')
            ->setParameter('inputType', InputType::Section);

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

    /**
     * @param array<int, string> $fieldTypeIds
     *
     * @throws QueryException
     */
    public function synchronizeColumns(
        User $user,
        array $fieldTypeIds,
    ): void {
        $entityManager = $this->getEntityManager();

        foreach ($this->getColumnsForUser($user) as $configuration) {
            $entityManager->remove($configuration);
        }

        $entityManager->flush();

        $fieldTypes = new ArrayCollection(
            $entityManager
                ->getRepository(FieldType::class)
                ->findBy(['id' => $fieldTypeIds]),
        );

        foreach ($fieldTypeIds as $position => $fieldTypeId) {
            $fieldType = $fieldTypes->findFirst(
                static fn (int|string $key, FieldType $fieldType): bool => (string) $fieldType->getId() === $fieldTypeId,
            );

            if (null === $fieldType) {
                continue;
            }

            $entityManager->persist(
                new ProductColumnConfiguration()
                    ->setUser($user)
                    ->setFieldType($fieldType)
                    ->setPosition($position),
            );
        }

        $entityManager->flush();
    }
}
