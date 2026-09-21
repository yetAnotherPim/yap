<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Repository\IdentifierTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IdentifierTypeRepository::class)]
class IdentifierTypeRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new IdentifierTypeRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(IdentifierTypeRepository::class, $repository);
    }

    public function testIsInUseReturnsTrueWhenUsedByIdentifier(): void
    {
        $registry = \Mockery::mock(ManagerRegistry::class);
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $query = \Mockery::mock(Query::class);
        $identifierType = \Mockery::mock(IdentifierType::class);

        $repository = \Mockery::mock(
            IdentifierTypeRepository::class,
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
                Identifier::class,
                'identifier',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('where')
            ->once()
            ->with('identifier.type = :identifierType')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with('identifierType', $identifierType)
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
            $repository->isInUse($identifierType),
        );
    }

    public function testIsInUseReturnsFalseWhenNotUsed(): void
    {
        $registry = \Mockery::mock(ManagerRegistry::class);
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $query = \Mockery::mock(Query::class);
        $identifierType = \Mockery::mock(IdentifierType::class);

        $repository = \Mockery::mock(
            IdentifierTypeRepository::class,
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
                Identifier::class,
                'identifier',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('where')
            ->once()
            ->with('identifier.type = :identifierType')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with('identifierType', $identifierType)
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
            $repository->isInUse($identifierType),
        );
    }
}
