<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductType>
 */
class ProductTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductType::class);
    }

    public function isInUse(ProductType $productType): bool
    {
        return null !== $this->getEntityManager()
                ->createQueryBuilder()
                ->select('1')
                ->from(Product::class, 'product')
                ->where('product.productType = :productType')
                ->setParameter('productType', $productType)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
    }
}
