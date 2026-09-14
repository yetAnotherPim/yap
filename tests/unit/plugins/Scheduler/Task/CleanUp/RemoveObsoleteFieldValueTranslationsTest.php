<?php

declare(strict_types=1);

namespace App\Tests\unit\plugins\Scheduler\Task\CleanUp;

use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\plugins\Scheduler\Task\AbstractTask;
use App\plugins\Scheduler\Task\CleanUp\RemoveObsoleteFieldValueTranslations;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RemoveObsoleteFieldValueTranslations::class)]
#[CoversClass(AbstractTask::class)]
final class RemoveObsoleteFieldValueTranslationsTest extends MockeryTestCase
{
    private EntityManagerInterface&MockInterface $entityManager;
    private Connection&MockInterface $connection;
    private QueryBuilder&MockInterface $queryBuilder;

    /** @var Query<int, FieldValueTranslation>&MockInterface */
    private Query&MockInterface $query;

    private RemoveObsoleteFieldValueTranslations $task;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = \Mockery::mock(EntityManagerInterface::class);
        $this->connection = \Mockery::mock(Connection::class);
        $this->queryBuilder = \Mockery::mock(QueryBuilder::class);
        $this->query = \Mockery::mock(Query::class);

        $this->task = new RemoveObsoleteFieldValueTranslations(
            $this->entityManager,
        );
    }

    public function testInvokeLogsExecutionAndRunsTask(): void
    {
        $translation = \Mockery::mock(FieldValueTranslation::class);

        $this->entityManager
            ->shouldReceive('getConnection')
            ->once()
            ->andReturn($this->connection);

        $this->connection
            ->shouldReceive('executeStatement')
            ->once()
            ->with(
                <<<'SQL'
                    INSERT INTO yap_scheduler (id, last_run)
                    VALUES (1, CURRENT_TIMESTAMP)
                    ON CONFLICT (id)
                    DO UPDATE SET last_run = CURRENT_TIMESTAMP
                SQL,
            );

        $this->expectQueryBuilder();

        $this->query
            ->shouldReceive('getResult')
            ->once()
            ->andReturn([$translation]);

        $this->entityManager
            ->shouldReceive('remove')
            ->once()
            ->with($translation);

        $this->entityManager
            ->shouldReceive('flush')
            ->once();

        ($this->task)();
    }

    public function testRunRemovesObsoleteTranslations(): void
    {
        $translation1 = \Mockery::mock(FieldValueTranslation::class);
        $translation2 = \Mockery::mock(FieldValueTranslation::class);

        $this->expectQueryBuilder();

        $this->query
            ->shouldReceive('getResult')
            ->once()
            ->andReturn([
                $translation1,
                $translation2,
            ]);

        $this->entityManager
            ->shouldReceive('remove')
            ->once()
            ->with($translation1);

        $this->entityManager
            ->shouldReceive('remove')
            ->once()
            ->with($translation2);

        $this->entityManager
            ->shouldReceive('flush')
            ->once();

        $this->task->run();
    }

    public function testRunFlushesWhenNoObsoleteTranslationsExist(): void
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
            ->with('translation')
            ->andReturnSelf();

        $this->queryBuilder
            ->shouldReceive('from')
            ->once()
            ->with(FieldValueTranslation::class, 'translation')
            ->andReturnSelf();

        $this->queryBuilder
            ->shouldReceive('join')
            ->once()
            ->with('translation.field', 'field')
            ->andReturnSelf();

        $this->queryBuilder
            ->shouldReceive('join')
            ->once()
            ->with('field.type', 'fieldType')
            ->andReturnSelf();

        $this->queryBuilder
            ->shouldReceive('where')
            ->once()
            ->with('fieldType.translatable = :translatable')
            ->andReturnSelf();

        $this->queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with('translatable', false)
            ->andReturnSelf();

        $this->queryBuilder
            ->shouldReceive('orderBy')
            ->once()
            ->with('translation.createdAt', 'ASC')
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

    public function testGetTranslationKey(): void
    {
        $this->assertEquals('remove_obsolete_field_value_translations', $this->task->getTranslationKey());
    }
}
