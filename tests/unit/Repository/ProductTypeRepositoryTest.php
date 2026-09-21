<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use App\Repository\ProductTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProductTypeRepository::class)]
class ProductTypeRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new ProductTypeRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(ProductTypeRepository::class, $repository);
    }

    public function testIsInUseReturnsTrueWhenUsedByProduct(): void
    {
        $registry = \Mockery::mock(ManagerRegistry::class);
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $query = \Mockery::mock(Query::class);
        $productType = \Mockery::mock(ProductType::class);

        $repository = \Mockery::mock(
            ProductTypeRepository::class,
            [$registry],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $repository
            ->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManager);

        $entityManager
            ->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($queryBuilder);

        $queryBuilder
            ->shouldReceive('select')
            ->once()
            ->with('1')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('from')
            ->once()
            ->with(
                Product::class,
                'product',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('where')
            ->once()
            ->with('product.productType = :productType')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with('productType', $productType)
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setMaxResults')
            ->once()
            ->with(1)
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('getQuery')
            ->once()
            ->andReturn($query);

        $query
            ->shouldReceive('getOneOrNullResult')
            ->once()
            ->andReturn(1);

        $this->assertTrue(
            $repository->isInUse($productType),
        );
    }

    public function testIsInUseReturnsFalseWhenNotUsed(): void
    {
        $registry = \Mockery::mock(ManagerRegistry::class);
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $query = \Mockery::mock(Query::class);
        $productType = \Mockery::mock(ProductType::class);

        $repository = \Mockery::mock(
            ProductTypeRepository::class,
            [$registry],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $repository
            ->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManager);

        $entityManager
            ->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($queryBuilder);

        $queryBuilder
            ->shouldReceive('select')
            ->once()
            ->with('1')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('from')
            ->once()
            ->with(
                Product::class,
                'product',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('where')
            ->once()
            ->with('product.productType = :productType')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with('productType', $productType)
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setMaxResults')
            ->once()
            ->with(1)
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('getQuery')
            ->once()
            ->andReturn($query);

        $query
            ->shouldReceive('getOneOrNullResult')
            ->once()
            ->andReturnNull();

        $this->assertFalse(
            $repository->isInUse($productType),
        );
    }
}
