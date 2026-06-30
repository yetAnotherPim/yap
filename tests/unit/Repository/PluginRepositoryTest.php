<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Entity\Plugin\Plugin;
use App\Repository\PluginRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\Entity\EntityPersister;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\ManagerRegistry;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PluginRepository::class)]
class PluginRepositoryTest extends MockeryTestCase
{
    /** @var ManagerRegistry|Mockery\MockInterface */
    private ManagerRegistry $registryMock;

    /** @var EntityManagerInterface|Mockery\MockInterface */
    private EntityManagerInterface $entityManagerMock;
    private PluginRepository $repository;

    public function setUp(): void
    {
        $this->registryMock = \Mockery::mock(ManagerRegistry::class);
        $this->entityManagerMock = \Mockery::mock(EntityManagerInterface::class);

        $this->repository = new PluginRepository($this->registryMock);

        parent::setUp();
    }

    public function testInstantiation(): void
    {
        $repository = new PluginRepository($this->registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(PluginRepository::class, $repository);
    }

    public function testDeleteOrphans(): void
    {
        $plugin1 = \Mockery::mock(Plugin::class);
        $plugin2 = \Mockery::mock(Plugin::class);
        /** @phpstan-var Collection<int, Plugin> $pluginCollection */
        $pluginCollection = new ArrayCollection([$plugin1, $plugin2]);

        $queryBuilderMock = \Mockery::mock(QueryBuilder::class);
        $expressionMock = \Mockery::mock(Expr::class);
        $expressionFunctionMock = \Mockery::mock(Expr\Func::class);
        $queryMock = \Mockery::mock(Query::class);

        $plugin1
            ->shouldReceive('getNamespace')
            ->once()
            ->andReturn('App\\plugin\\SamplePlugin\\Plugin');

        $plugin2
            ->shouldReceive('getNamespace')
            ->once()
            ->andReturn('App\\plugin\\TestPlugin\\Plugin');

        $this->registryMock
            ->shouldReceive('getManagerForClass')
            ->once()
            ->andReturn($this->entityManagerMock);

        $this->entityManagerMock
            ->shouldReceive('getClassMetadata')
            ->once()
            ->andReturn(new ClassMetadata(Plugin::class));

        $this->entityManagerMock
            ->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($queryBuilderMock);

        $queryBuilderMock
            ->shouldReceive('select')
            ->once()
            ->with('plugin')
            ->andReturn($queryBuilderMock);

        $queryBuilderMock
            ->shouldReceive('from')
            ->once()
            ->with(Plugin::class, 'plugin', null)
            ->andReturn($queryBuilderMock);

        $queryBuilderMock
            ->shouldReceive('delete')
            ->once()
            ->andReturn($queryBuilderMock);

        $queryBuilderMock
            ->shouldReceive('expr')
            ->once()
            ->andReturn($expressionMock);

        $expressionMock
            ->shouldReceive('notIn')
            ->once()
            ->with('plugin.namespace', ['App\\plugin\\SamplePlugin\\Plugin', 'App\\plugin\\TestPlugin\\Plugin'])
            ->andReturn($expressionFunctionMock);

        $queryBuilderMock
            ->shouldReceive('where')
            ->once()
            ->with($expressionFunctionMock)
            ->andReturn($queryBuilderMock);

        $queryBuilderMock
            ->shouldReceive('getQuery')
            ->once()
            ->andReturn($queryMock);

        $queryMock
            ->shouldReceive('execute')
            ->once();

        $this->repository->deleteOrphans($pluginCollection);
    }

    public function testGetActivePlugins(): void
    {
        $unitOfWorkMock = \Mockery::mock(UnitOfWork::class);
        $entityPersisterMock = \Mockery::mock(EntityPersister::class);

        $this->registryMock
            ->shouldReceive('getManagerForClass')
            ->once()
            ->andReturn($this->entityManagerMock);

        $this->entityManagerMock
            ->shouldReceive('getClassMetadata')
            ->once()
            ->andReturn(new ClassMetadata(Plugin::class));

        $this->entityManagerMock
            ->shouldReceive('getUnitOfWork')
            ->once()
            ->andReturn($unitOfWorkMock);

        $unitOfWorkMock
            ->shouldReceive('getEntityPersister')
            ->once()
            ->with(Plugin::class)
            ->andReturn($entityPersisterMock);

        $entityPersisterMock
            ->shouldReceive('loadAll')
            ->withSomeOfArgs(['active' => true])
            ->once()
            ->andReturn([\Mockery::mock(Plugin::class)]);

        $this->repository->getActivePlugins();
    }

    public function testGetPluginEntity(): void
    {
        $unitOfWorkMock = \Mockery::mock(UnitOfWork::class);
        $entityPersisterMock = \Mockery::mock(EntityPersister::class);

        $this->registryMock
            ->shouldReceive('getManagerForClass')
            ->once()
            ->andReturn($this->entityManagerMock);

        $this->entityManagerMock
            ->shouldReceive('getClassMetadata')
            ->once()
            ->andReturn(new ClassMetadata(Plugin::class));

        $this->entityManagerMock
            ->shouldReceive('getUnitOfWork')
            ->once()
            ->andReturn($unitOfWorkMock);

        $unitOfWorkMock
            ->shouldReceive('getEntityPersister')
            ->once()
            ->with(Plugin::class)
            ->andReturn($entityPersisterMock);

        $entityPersisterMock
            ->shouldReceive('load')
            ->withSomeOfArgs(['namespace' => 'App\\plugin\\SamplePlugin\\Plugin'])
            ->once()
            ->andReturn(\Mockery::mock(Plugin::class));

        $this->repository->getPluginEntity('App\\plugin\\SamplePlugin\\Plugin');
    }
}
