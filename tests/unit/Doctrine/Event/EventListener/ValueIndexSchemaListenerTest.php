<?php

declare(strict_types=1);

namespace App\Tests\unit\Doctrine\Event\EventListener;

use App\Doctrine\Event\EventListener\ValueIndexSchemaListener;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ValueIndexSchemaListener::class)]
final class ValueIndexSchemaListenerTest extends MockeryTestCase
{
    public function testAddsIndexesWithPrefixLengthForMySql(): void
    {
        $resultSchema = $this->executeListener(
            $this->createSchema(),
            \Mockery::mock(AbstractMySQLPlatform::class),
        );

        $fieldIndex = $resultSchema
            ->getTable('field')
            ->getIndex('index_product_field_value');

        $translationIndex = $resultSchema
            ->getTable('field_value_translation')
            ->getIndex('index_product_field_translation_value');

        $this->assertSame(
            ['value'],
            $fieldIndex->getColumns(),
        );

        $this->assertSame(
            [768],
            $fieldIndex->getOption('lengths'),
        );

        $this->assertSame(
            ['value'],
            $translationIndex->getColumns(),
        );

        $this->assertSame(
            [768],
            $translationIndex->getOption('lengths'),
        );
    }

    public function testAddsFullIndexesForPostgreSql(): void
    {
        $resultSchema = $this->executeListener(
            $this->createSchema(),
            \Mockery::mock(PostgreSQLPlatform::class),
        );

        $fieldIndex = $resultSchema
            ->getTable('field')
            ->getIndex('index_product_field_value');

        $translationIndex = $resultSchema
            ->getTable('field_value_translation')
            ->getIndex('index_product_field_translation_value');

        $this->assertSame(
            ['value'],
            $fieldIndex->getColumns(),
        );

        $this->assertFalse(
            $fieldIndex->hasOption('lengths'),
        );

        $this->assertSame(
            ['value'],
            $translationIndex->getColumns(),
        );

        $this->assertFalse(
            $translationIndex->hasOption('lengths'),
        );
    }

    public function testDoesNotModifyExistingIndexes(): void
    {
        $schema = $this->createSchema();

        $schema
            ->getTable('field')
            ->addIndex(
                ['value'],
                'index_product_field_value',
            );

        $schema
            ->getTable('field_value_translation')
            ->addIndex(
                ['value'],
                'index_product_field_translation_value',
            );

        $resultSchema = $this->executeListener(
            $schema,
            \Mockery::mock(PostgreSQLPlatform::class),
        );

        $fieldIndexes = array_filter(
            $resultSchema->getTable('field')->getIndexes(),
            static fn ($index): bool => 'index_product_field_value'
                === $index->getName(),
        );

        $translationIndexes = array_filter(
            $resultSchema
                ->getTable('field_value_translation')
                ->getIndexes(),
            static fn ($index): bool => 'index_product_field_translation_value'
                === $index->getName(),
        );

        $this->assertCount(1, $fieldIndexes);
        $this->assertCount(1, $translationIndexes);
    }

    private function createSchema(): Schema
    {
        $schema = new Schema();

        $schema
            ->createTable('field')
            ->addColumn('value', 'text');

        $schema
            ->createTable('field_value_translation')
            ->addColumn('value', 'text');

        return $schema;
    }

    private function executeListener(
        Schema $schema,
        AbstractMySQLPlatform|PostgreSQLPlatform $platform,
    ): Schema {
        $connection = \Mockery::mock(Connection::class);

        $entityManager = \Mockery::mock(EntityManagerInterface::class);

        $event = \Mockery::mock(GenerateSchemaEventArgs::class);

        $connection
            ->shouldReceive('getDatabasePlatform')
            ->once()
            ->andReturn($platform);

        $entityManager
            ->shouldReceive('getConnection')
            ->once()
            ->andReturn($connection);

        $event
            ->shouldReceive('getSchema')
            ->once()
            ->andReturn($schema);

        $event
            ->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManager);

        $resultSchema = null;

        $event
            ->shouldReceive('setSchema')
            ->once()
            ->with(
                \Mockery::on(
                    static function (
                        Schema $schema,
                    ) use (&$resultSchema): bool {
                        $resultSchema = $schema;

                        return true;
                    },
                ),
            );

        (new ValueIndexSchemaListener())->postGenerateSchema($event);

        $this->assertInstanceOf(
            Schema::class,
            $resultSchema,
        );

        return $resultSchema;
    }
}
