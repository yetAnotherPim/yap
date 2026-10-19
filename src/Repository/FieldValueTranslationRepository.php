<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product\Field\Translation\FieldValueTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FieldValueTranslation>
 */
class FieldValueTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FieldValueTranslation::class);
    }
}
