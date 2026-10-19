<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProductRepository::class)]
class ProductRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new ProductRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(ProductRepository::class, $repository);
    }

    public function testCountCreatedWithinLastDays(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);
        $repository = \Mockery::mock(ProductRepository::class, [$registryMock])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $qb = \Mockery::mock(\Doctrine\ORM\QueryBuilder::class);
        $query = \Mockery::mock(\Doctrine\ORM\Query::class);

        $repository->shouldReceive('createQueryBuilder')
            ->with('p')
            ->once()
            ->andReturn($qb);

        $qb->shouldReceive('select')->with('COUNT(p.id)')->once()->andReturnSelf();
        $qb->shouldReceive('andWhere')->with('p.createdAt >= :since')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('since', \Mockery::type(\DateTimeImmutable::class))->once()->andReturnSelf();
        $qb->shouldReceive('getQuery')->once()->andReturn($query);
        $query->shouldReceive('getSingleScalarResult')->once()->andReturn(5);

        $result = $repository->countCreatedWithinLastDays(10);

        $this->assertSame(5, $result);
    }
}
