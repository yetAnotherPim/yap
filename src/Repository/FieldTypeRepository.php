<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Messenger\ProductUpdate\Message\Dto\FieldTypeIdentifier;
use App\Messenger\ProductUpdate\Message\Enum\FieldTypeIdentifierType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FieldType>
 */
class FieldTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FieldType::class);
    }

    public function isInUse(FieldType $fieldType): bool
    {
        $configurationExists = null !== $this->getEntityManager()
                ->createQueryBuilder()
                ->select('1')
                ->from(ProductTypeFieldConfiguration::class, 'configuration')
                ->where('configuration.fieldType = :fieldType')
                ->setParameter('fieldType', $fieldType)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

        if ($configurationExists) {
            return true;
        }

        return null !== $this->getEntityManager()
                ->createQueryBuilder()
                ->select('1')
                ->from(Field::class, 'field')
                ->where('field.type = :fieldType')
                ->setParameter('fieldType', $fieldType)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
    }

    public function findByIdentifier(FieldTypeIdentifier $identifier): ?FieldType
    {
        return match ($identifier->getType()) {
            FieldTypeIdentifierType::ID => $this->find($identifier->getValue()),
            FieldTypeIdentifierType::CODE => $this->findOneBy(['code' => $identifier->getValue()]),
        };
    }
}
