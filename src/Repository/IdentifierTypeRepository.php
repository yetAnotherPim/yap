<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Identifier\Type\IdentifierType;
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
}
