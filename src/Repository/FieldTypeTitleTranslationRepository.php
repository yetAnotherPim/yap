<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product\Field\Type\Translation\FieldTypeTitleTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FieldTypeTitleTranslation>
 */
class FieldTypeTitleTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FieldTypeTitleTranslation::class);
    }
}
