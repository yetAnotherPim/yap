<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\User\Configuration\ProductColumnConfiguration;
use App\Entity\User\User;
use App\Repository\ProductColumnConfigurationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(ProductColumnConfigurationRepository::class)]
#[UsesClass(ProductColumnConfiguration::class)]
class ProductColumnConfigurationRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new ProductColumnConfigurationRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(ProductColumnConfigurationRepository::class, $repository);
    }

    public function testGetColumnsForUser(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $user = \Mockery::mock(User::class);

        $productColumnConfiguration1 = \Mockery::mock(
            ProductColumnConfiguration::class,
        );
        $productColumnConfiguration2 = \Mockery::mock(
            ProductColumnConfiguration::class,
        );

        $queryMock = \Mockery::mock(\Doctrine\ORM\Query::class);

        $queryBuilderMock = \Mockery::mock(QueryBuilder::class);

        $repository = \Mockery::mock(
            ProductColumnConfigurationRepository::class,
            [$registryMock],
        )->makePartial();

        $repository
            ->shouldReceive('createQueryBuilder')
            ->once()
            ->with('configuration')
            ->andReturn($queryBuilderMock);

        $queryBuilderMock
            ->shouldReceive('indexBy')
            ->once()
            ->with('configuration', 'configuration.fieldType')
            ->andReturnSelf();

        $queryBuilderMock
            ->shouldReceive('andWhere')
            ->once()
            ->with('configuration.user = :user')
            ->andReturnSelf();

        $queryBuilderMock
            ->shouldReceive('setParameter')
            ->once()
            ->with('user', $user)
            ->andReturnSelf();

        $queryBuilderMock
            ->shouldReceive('orderBy')
            ->once()
            ->with('configuration.position', 'ASC')
            ->andReturnSelf();

        $queryBuilderMock
            ->shouldReceive('getQuery')
            ->once()
            ->andReturn($queryMock);

        $queryMock
            ->shouldReceive('getResult')
            ->once()
            ->andReturn([
                'field-type-1' => $productColumnConfiguration1,
                'field-type-2' => $productColumnConfiguration2,
            ]);

        $result = $repository->getColumnsForUser($user);

        $this->assertInstanceOf(
            Collection::class,
            $result,
        );
        $this->assertCount(2, $result);
        $this->assertSame(
            $productColumnConfiguration1,
            $result->first(),
        );
        $this->assertSame(
            $productColumnConfiguration2,
            $result->last(),
        );
        $this->assertSame(
            $productColumnConfiguration1,
            $result->get('field-type-1'),
        );
        $this->assertSame(
            $productColumnConfiguration2,
            $result->get('field-type-2'),
        );
    }

    public function testGetNotConfiguredFieldTypesWithoutConfigurations(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);
        $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $fieldTypeRepositoryMock = \Mockery::mock(
            \Doctrine\ORM\EntityRepository::class,
        );
        $queryBuilderMock = \Mockery::mock(QueryBuilder::class);
        $queryMock = \Mockery::mock(\Doctrine\ORM\Query::class);

        $repository = \Mockery::mock(
            ProductColumnConfigurationRepository::class,
            [$registryMock],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $repository
            ->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManagerMock);

        $entityManagerMock
            ->shouldReceive('getRepository')
            ->once()
            ->with(FieldType::class)
            ->andReturn($fieldTypeRepositoryMock);

        $fieldTypeRepositoryMock
            ->shouldReceive('createQueryBuilder')
            ->once()
            ->with('fieldType')
            ->andReturn($queryBuilderMock);

        $queryBuilderMock
            ->shouldReceive('andWhere')
            ->once()
            ->with('fieldType.inputType != :inputType')
            ->andReturnSelf();

        $queryBuilderMock
            ->shouldReceive('setParameter')
            ->once()
            ->with('inputType', InputType::Section)
            ->andReturnSelf();

        $queryBuilderMock
            ->shouldNotReceive('setParameter')
            ->with('fieldTypeIds', \Mockery::any());

        $queryBuilderMock
            ->shouldReceive('getQuery')
            ->once()
            ->andReturn($queryMock);

        $fieldType1 = \Mockery::mock(FieldType::class);
        $fieldType2 = \Mockery::mock(FieldType::class);

        $queryMock
            ->shouldReceive('getResult')
            ->once()
            ->andReturn([$fieldType1, $fieldType2]);

        $result = $repository->getNotConfiguredFieldTypes(
            new ArrayCollection(),
        );

        $this->assertCount(2, $result);
        $this->assertSame($fieldType1, $result->first());
        $this->assertSame($fieldType2, $result->last());
    }

    public function testGetNotConfiguredFieldTypesWithConfigurations(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);
        $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $fieldTypeRepositoryMock = \Mockery::mock(
            \Doctrine\ORM\EntityRepository::class,
        );
        $queryBuilderMock = \Mockery::mock(QueryBuilder::class);
        $queryMock = \Mockery::mock(\Doctrine\ORM\Query::class);

        $configuration1 = \Mockery::mock(
            ProductColumnConfiguration::class,
        );
        $configuration2 = \Mockery::mock(
            ProductColumnConfiguration::class,
        );

        $fieldType1 = \Mockery::mock(FieldType::class);
        $fieldType2 = \Mockery::mock(FieldType::class);

        $repository = \Mockery::mock(
            ProductColumnConfigurationRepository::class,
            [$registryMock],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $repository
            ->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManagerMock);

        $entityManagerMock
            ->shouldReceive('getRepository')
            ->once()
            ->with(FieldType::class)
            ->andReturn($fieldTypeRepositoryMock);

        $fieldTypeRepositoryMock
            ->shouldReceive('createQueryBuilder')
            ->once()
            ->with('fieldType')
            ->andReturn($queryBuilderMock);

        $queryBuilderMock
            ->shouldReceive('andWhere')
            ->once()
            ->with('fieldType.inputType != :inputType')
            ->andReturnSelf();

        $queryBuilderMock
            ->shouldReceive('setParameter')
            ->once()
            ->with('inputType', InputType::Section)
            ->andReturnSelf();

        $queryBuilderMock
            ->shouldReceive('andWhere')
            ->once()
            ->with('fieldType.id NOT IN (:fieldTypeIds)')
            ->andReturnSelf();

        $queryBuilderMock
            ->shouldReceive('setParameter')
            ->once()
            ->with(
                'fieldTypeIds',
                ['field-type-1', 'field-type-2'],
            )
            ->andReturnSelf();

        $queryBuilderMock
            ->shouldReceive('getQuery')
            ->once()
            ->andReturn($queryMock);

        $queryMock
            ->shouldReceive('getResult')
            ->once()
            ->andReturn([$fieldType1, $fieldType2]);

        /** @var Collection<string, ProductColumnConfiguration> $configurations */
        $configurations = new ArrayCollection([
            'field-type-1' => $configuration1,
            'field-type-2' => $configuration2,
        ]);

        $result = $repository->getNotConfiguredFieldTypes($configurations);

        $this->assertCount(2, $result);
        $this->assertSame($fieldType1, $result->first());
        $this->assertSame($fieldType2, $result->last());
    }

    public function testSynchronizeColumnsWithEmptyFieldTypeIds(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);
        $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $fieldTypeRepositoryMock = \Mockery::mock(
            \Doctrine\ORM\EntityRepository::class,
        );

        $configuration = \Mockery::mock(
            ProductColumnConfiguration::class,
        );

        $user = \Mockery::mock(User::class);

        $repository = \Mockery::mock(
            ProductColumnConfigurationRepository::class,
            [$registryMock],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $repository
            ->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManagerMock);

        $repository
            ->shouldReceive('getColumnsForUser')
            ->once()
            ->with($user)
            ->andReturn(new ArrayCollection([$configuration]));

        $entityManagerMock
            ->shouldReceive('remove')
            ->once()
            ->with($configuration);

        $entityManagerMock
            ->shouldReceive('flush')
            ->twice();

        $entityManagerMock
            ->shouldReceive('getRepository')
            ->once()
            ->with(FieldType::class)
            ->andReturn($fieldTypeRepositoryMock);

        $fieldTypeRepositoryMock
            ->shouldReceive('findBy')
            ->once()
            ->with(['id' => []])
            ->andReturn([]);

        $entityManagerMock
            ->shouldNotReceive('persist');

        $repository->synchronizeColumns($user, []);
    }

    public function testSynchronizeColumnsPersistsFieldTypesInGivenOrder(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);
        $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $fieldTypeRepositoryMock = \Mockery::mock(
            \Doctrine\ORM\EntityRepository::class,
        );

        $user = \Mockery::mock(User::class);

        $fieldType1 = \Mockery::mock(FieldType::class);
        $fieldType2 = \Mockery::mock(FieldType::class);

        $fieldType1Id = UuidV7::v7();
        $fieldType2Id = UuidV7::v7();

        $fieldType1
            ->shouldReceive('getId')
            ->andReturn($fieldType1Id);

        $fieldType2
            ->shouldReceive('getId')
            ->andReturn($fieldType2Id);

        $repository = \Mockery::mock(
            ProductColumnConfigurationRepository::class,
            [$registryMock],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $repository
            ->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManagerMock);

        $repository
            ->shouldReceive('getColumnsForUser')
            ->once()
            ->with($user)
            ->andReturn(new ArrayCollection());

        $entityManagerMock
            ->shouldReceive('flush')
            ->twice();

        $entityManagerMock
            ->shouldReceive('getRepository')
            ->once()
            ->with(FieldType::class)
            ->andReturn($fieldTypeRepositoryMock);

        $fieldTypeRepositoryMock
            ->shouldReceive('findBy')
            ->once()
            ->with([
                'id' => [
                    $fieldType1Id->toString(),
                    $fieldType2Id->toString(),
                ],
            ])
            ->andReturn([$fieldType1, $fieldType2]);

        $persisted = [];

        $entityManagerMock
            ->shouldReceive('persist')
            ->twice()
            ->withArgs(
                function (
                    ProductColumnConfiguration $configuration,
                ) use (&$persisted): bool {
                    $persisted[] = $configuration;

                    return true;
                },
            );

        $repository->synchronizeColumns(
            $user,
            [
                $fieldType1Id->toString(),
                $fieldType2Id->toString(),
            ],
        );

        $this->assertCount(2, $persisted);

        $this->assertSame($user, $persisted[0]->getUser());
        $this->assertSame($fieldType1, $persisted[0]->getFieldType());
        $this->assertSame(0, $persisted[0]->getPosition());

        $this->assertSame($user, $persisted[1]->getUser());
        $this->assertSame($fieldType2, $persisted[1]->getFieldType());
        $this->assertSame(1, $persisted[1]->getPosition());
    }

    public function testSynchronizeColumnsSkipsUnknownFieldTypeIds(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);
        $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $fieldTypeRepositoryMock = \Mockery::mock(
            \Doctrine\ORM\EntityRepository::class,
        );

        $user = \Mockery::mock(User::class);

        $fieldType = \Mockery::mock(FieldType::class);

        $knownId = UuidV7::v7();
        $unknownId = UuidV7::v7();

        $fieldType
            ->shouldReceive('getId')
            ->andReturn($knownId);

        $repository = \Mockery::mock(
            ProductColumnConfigurationRepository::class,
            [$registryMock],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $repository
            ->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManagerMock);

        $repository
            ->shouldReceive('getColumnsForUser')
            ->once()
            ->with($user)
            ->andReturn(new ArrayCollection());

        $entityManagerMock
            ->shouldReceive('flush')
            ->twice();

        $entityManagerMock
            ->shouldReceive('getRepository')
            ->once()
            ->with(FieldType::class)
            ->andReturn($fieldTypeRepositoryMock);

        $fieldTypeRepositoryMock
            ->shouldReceive('findBy')
            ->once()
            ->with([
                'id' => [
                    $knownId->toString(),
                    $unknownId->toString(),
                ],
            ])
            ->andReturn([$fieldType]);

        $entityManagerMock
            ->shouldReceive('persist')
            ->once()
            ->withArgs(
                function (
                    ProductColumnConfiguration $configuration,
                ) use (
                    $user,
                    $fieldType,
                ): bool {
                    $this->assertSame($user, $configuration->getUser());
                    $this->assertSame(
                        $fieldType,
                        $configuration->getFieldType(),
                    );
                    $this->assertSame(0, $configuration->getPosition());

                    return true;
                },
            );

        $repository->synchronizeColumns(
            $user,
            [
                $knownId->toString(),
                $unknownId->toString(),
            ],
        );
    }
}
