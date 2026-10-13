<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Messenger\ProductUpdate\Message\Dto\FieldTypeIdentifier;
use App\Messenger\ProductUpdate\Message\Enum\FieldTypeIdentifierType;
use App\Repository\FieldTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldTypeRepository::class)]
class FieldTypeRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new FieldTypeRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(FieldTypeRepository::class, $repository);
    }

    public function testIsInUseReturnsTrueWhenUsedByConfiguration(): void
    {
        $registry = \Mockery::mock(ManagerRegistry::class);
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $query = \Mockery::mock(Query::class);
        $fieldType = \Mockery::mock(FieldType::class);

        $repository = \Mockery::mock(
            FieldTypeRepository::class,
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
                ProductTypeFieldConfiguration::class,
                'configuration',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('where')
            ->once()
            ->with('configuration.fieldType = :fieldType')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with('fieldType', $fieldType)
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
            $repository->isInUse($fieldType),
        );
    }

    public function testIsInUseReturnsTrueWhenUsedByField(): void
    {
        $registry = \Mockery::mock(ManagerRegistry::class);
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $query = \Mockery::mock(Query::class);
        $fieldType = \Mockery::mock(FieldType::class);

        $repository = \Mockery::mock(
            FieldTypeRepository::class,
            [$registry],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $repository
            ->shouldReceive('getEntityManager')
            ->twice()
            ->andReturn($entityManager);

        $entityManager
            ->shouldReceive('createQueryBuilder')
            ->twice()
            ->andReturn($queryBuilder);

        $queryBuilder
            ->shouldReceive('select')
            ->twice()
            ->with('1')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('from')
            ->once()
            ->with(
                ProductTypeFieldConfiguration::class,
                'configuration',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('from')
            ->once()
            ->with(
                Field::class,
                'field',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('where')
            ->once()
            ->with('configuration.fieldType = :fieldType')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('where')
            ->once()
            ->with('field.type = :fieldType')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->twice()
            ->with('fieldType', $fieldType)
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setMaxResults')
            ->twice()
            ->with(1)
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('getQuery')
            ->twice()
            ->andReturn($query);

        $query
            ->shouldReceive('getOneOrNullResult')
            ->twice()
            ->andReturn(
                null,
                1,
            );

        $this->assertTrue(
            $repository->isInUse($fieldType),
        );
    }

    public function testIsInUseReturnsFalseWhenNotUsed(): void
    {
        $registry = \Mockery::mock(ManagerRegistry::class);
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $query = \Mockery::mock(Query::class);
        $fieldType = \Mockery::mock(FieldType::class);

        $repository = \Mockery::mock(
            FieldTypeRepository::class,
            [$registry],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $repository
            ->shouldReceive('getEntityManager')
            ->twice()
            ->andReturn($entityManager);

        $entityManager
            ->shouldReceive('createQueryBuilder')
            ->twice()
            ->andReturn($queryBuilder);

        $queryBuilder
            ->shouldReceive('select')
            ->twice()
            ->with('1')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('from')
            ->once()
            ->with(
                ProductTypeFieldConfiguration::class,
                'configuration',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('from')
            ->once()
            ->with(
                Field::class,
                'field',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('where')
            ->once()
            ->with('configuration.fieldType = :fieldType')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('where')
            ->once()
            ->with('field.type = :fieldType')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->twice()
            ->with('fieldType', $fieldType)
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setMaxResults')
            ->twice()
            ->with(1)
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('getQuery')
            ->twice()
            ->andReturn($query);

        $query
            ->shouldReceive('getOneOrNullResult')
            ->twice()
            ->andReturnNull();

        $this->assertFalse(
            $repository->isInUse($fieldType),
        );
    }

    public function testFindByIdentifierWithId(): void
    {
        $registry = \Mockery::mock(ManagerRegistry::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $identifier = \Mockery::mock(FieldTypeIdentifier::class);

        $identifier->shouldReceive('getType')
            ->once()
            ->andReturn(FieldTypeIdentifierType::ID);

        $identifier->shouldReceive('getValue')
            ->once()
            ->andReturn('42');
        $repository = \Mockery::mock(FieldTypeRepository::class, [$registry])
            ->makePartial();

        $repository->shouldReceive('find')
            ->once()
            ->with('42')
            ->andReturn($fieldType);

        $this->assertSame($fieldType, $repository->findByIdentifier($identifier));
    }

    public function testFindByIdentifierWithCode(): void
    {
        $registry = \Mockery::mock(ManagerRegistry::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $identifier = \Mockery::mock(FieldTypeIdentifier::class);

        $identifier->shouldReceive('getType')
            ->once()
            ->andReturn(FieldTypeIdentifierType::CODE);

        $identifier->shouldReceive('getValue')
            ->once()
            ->andReturn('product_name');

        $repository = \Mockery::mock(FieldTypeRepository::class, [$registry])
            ->makePartial();

        $repository->shouldReceive('findOneBy')
            ->once()
            ->with(['code' => 'product_name'])
            ->andReturn($fieldType);

        $this->assertSame($fieldType, $repository->findByIdentifier($identifier));
    }

    public function testFindByIdentifierReturnsNullWhenNotFound(): void
    {
        $registry = \Mockery::mock(ManagerRegistry::class);
        $identifier = \Mockery::mock(FieldTypeIdentifier::class);

        $identifier->shouldReceive('getType')
            ->once()
            ->andReturn(FieldTypeIdentifierType::CODE);

        $identifier->shouldReceive('getValue')
            ->once()
            ->andReturn('unknown_field');

        $repository = \Mockery::mock(FieldTypeRepository::class, [$registry])
            ->makePartial();

        $repository->shouldReceive('findOneBy')
            ->once()
            ->with(['code' => 'unknown_field'])
            ->andReturnNull();

        $this->assertNull($repository->findByIdentifier($identifier));
    }
}
