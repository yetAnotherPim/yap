<?php

declare(strict_types=1);

namespace App\Doctrine\Event\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaEditor;
use Doctrine\DBAL\Schema\TableEditor;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;

#[AsDoctrineListener(event: ToolEvents::postGenerateSchema)]
final class ValueIndexSchemaListener
{
    private const string FIELD_TABLE = 'field';
    private const string FIELD_VALUE_INDEX = 'index_product_field_value';

    private const string FIELD_TRANSLATION_TABLE = 'field_value_translation';
    private const string FIELD_TRANSLATION_VALUE_INDEX = 'index_product_field_translation_value';

    private const int MYSQL_INDEX_PREFIX_LENGTH = 768;

    /**
     * @throws Exception
     */
    public function postGenerateSchema(
        GenerateSchemaEventArgs $event,
    ): void {
        $schema = $event->getSchema();

        $platform = $event
            ->getEntityManager()
            ->getConnection()
            ->getDatabasePlatform();

        $schemaEditor = $schema->edit();

        $this->addValueIndex(
            $schema,
            $schemaEditor,
            self::FIELD_TABLE,
            self::FIELD_VALUE_INDEX,
            $platform,
        );

        $this->addValueIndex(
            $schema,
            $schemaEditor,
            self::FIELD_TRANSLATION_TABLE,
            self::FIELD_TRANSLATION_VALUE_INDEX,
            $platform,
        );

        $event->setSchema(
            $schemaEditor->create(),
        );
    }

    /**
     * @param non-empty-string $tableName
     */
    private function addValueIndex(
        Schema $schema,
        SchemaEditor $schemaEditor,
        string $tableName,
        string $indexName,
        AbstractPlatform $platform,
    ): void {
        $table = $schema->getTable($tableName);

        if ($table->hasIndex($indexName)) {
            return;
        }

        $schemaEditor->modifyTableByUnquotedName(
            $tableName,
            function (TableEditor $tableEditor) use (
                $indexName,
                $platform,
            ): void {
                $options = [];

                if ($platform instanceof AbstractMySQLPlatform) {
                    $options['lengths'] = [
                        self::MYSQL_INDEX_PREFIX_LENGTH,
                    ];
                }

                $tableEditor->addIndex(
                    new Index(
                        $indexName,
                        ['value'],
                        options: $options,
                    ),
                );
            },
        );
    }
}
