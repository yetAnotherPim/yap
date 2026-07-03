<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product\Field\Type\Section\FieldSection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FieldSection>
 */
class FieldSectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FieldSection::class);
    }
}
