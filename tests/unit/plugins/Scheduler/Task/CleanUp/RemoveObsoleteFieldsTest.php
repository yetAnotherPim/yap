<?php

declare(strict_types=1);

namespace App\Tests\unit\plugins\Scheduler\Task\CleanUp;

use App\Entity\Product\Field\Field;
use App\plugins\Scheduler\Task\CleanUp\RemoveObsoleteFields;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RemoveObsoleteFields::class)]
class RemoveObsoleteFieldsTest extends MockeryTestCase
{
    private EntityManagerInterface&MockInterface $entityManager;
    private QueryBuilder&MockInterface $queryBuilder;

    /** @var Query<int, Field>&MockInterface */
    private Query&MockInterface $query;

    private RemoveObsoleteFields $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->queryBuilder = \Mockery::mock(QueryBuilder::class);

        /** @var Query<int, Field>&MockInterface $query */
        $query = \Mockery::mock(Query::class);
        $this->query = $query;

        $this->task = new RemoveObsoleteFields(
            $this->entityManager,
        );
    }

    public function testRunRemovesObsoleteFields(): void
    {
        $field1 = \Mockery::mock(Field::class);
        $field2 = \Mockery::mock(Field::class);

        $this->expectQueryBuilder();

        $this->query
            ->shouldReceive('getResult')
            ->once()
            ->andReturn([
                $field1,
                $field2,
            ]);

        $this->entityManager
            ->shouldReceive('remove')
            ->once()
            ->with($field1);

        $this->entityManager
            ->shouldReceive('remove')
            ->once()
            ->with($field2);

        $this->entityManager
            ->shouldReceive('flush')
            ->once();

        $this->task->run();
    }

    public function testRunFlushesWhenNoObsoleteFieldsExist(): void
    {
        $this->expectQueryBuilder();

        $this->query
            ->shouldReceive('getResult')
            ->once()
            ->andReturn([]);

        $this->entityManager
            ->shouldNotReceive('remove');

        $this->entityManager
            ->shouldReceive('flush')
            ->once();

        $this->task->run();
    }

    private function expectQueryBuilder(): void
    {
        $this->entityManager
            ->shouldReceive('createQueryBuilder')
            ->once()
            ->andReturn($this->queryBuilder);

        $this->queryBuilder
            ->shouldReceive('select')
            ->once()
            ->with('field')
            ->andReturnSelf();

        $this->queryBuilder
            ->shouldReceive('from')
            ->once()
            ->with(Field::class, 'field')
            ->andReturnSelf();

        $this->queryBuilder
            ->shouldReceive('join')
            ->once()
            ->with('field.product', 'product')
            ->andReturnSelf();

        $this->queryBuilder
            ->shouldReceive('join')
            ->once()
            ->with('product.productType', 'productType')
            ->andReturnSelf();

        $this->queryBuilder
            ->shouldReceive('join')
            ->once()
            ->with('field.type', 'fieldType')
            ->andReturnSelf();

        $this->queryBuilder
            ->shouldReceive('leftJoin')
            ->once()
            ->with(
                'productType.productTypeFieldConfigurations',
                'configuration',
                'WITH',
                'configuration.fieldType = fieldType',
            )
            ->andReturnSelf();

        $this->queryBuilder
            ->shouldReceive('where')
            ->once()
            ->with('configuration.id IS NULL')
            ->andReturnSelf();

        $this->queryBuilder
            ->shouldReceive('orderBy')
            ->once()
            ->with('field.createdAt', 'ASC')
            ->andReturnSelf();

        $this->queryBuilder
            ->shouldReceive('setMaxResults')
            ->once()
            ->with(25)
            ->andReturnSelf();

        $this->queryBuilder
            ->shouldReceive('getQuery')
            ->once()
            ->andReturn($this->query);
    }
}
