<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Product\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function filterTranslations(string $locale): ?Collection {
        $queryBuilder = $this->createQueryBuilder('product');

        $result = $queryBuilder
            ->join('p.fields', 'field')
            ->join('f.translations', 'translation')
            ->join('t.locale', 'locale')
            ->andWhere('locale.id = :locale')
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult();
    }
}
