<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Identifier\Type\IdentifierType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IdentifierType>
 */
class IdentifierTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IdentifierType::class);
    }

    public function isInUse(IdentifierType $identifierType): bool
    {
        return null !== $this->getEntityManager()
                ->createQueryBuilder()
                ->select('1')
                ->from(Identifier::class, 'identifier')
                ->where('identifier.type = :identifierType')
                ->setParameter('identifierType', $identifierType)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
    }
}
