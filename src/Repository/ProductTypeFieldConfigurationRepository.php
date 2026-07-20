<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductTypeFieldConfiguration>
 */
class ProductTypeFieldConfigurationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductTypeFieldConfiguration::class);
    }
}
