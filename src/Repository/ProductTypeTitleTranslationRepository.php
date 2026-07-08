<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product\Type\Translation\ProductTypeTitleTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductTypeTitleTranslation>
 */
class ProductTypeTitleTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductTypeTitleTranslation::class);
    }
}
