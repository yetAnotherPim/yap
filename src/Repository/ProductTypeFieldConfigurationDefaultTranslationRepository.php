<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product\Type\FieldConfiguration\DefaultTranslation\ProductTypeFieldConfigurationDefaultTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductTypeFieldConfigurationDefaultTranslation>
 */
class ProductTypeFieldConfigurationDefaultTranslationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductTypeFieldConfigurationDefaultTranslation::class);
    }
}
