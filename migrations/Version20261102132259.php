<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261102132259 extends AbstractMigration
{
    private const INDEX_NAME = 'index_product_field_translation_value';

    public function getDescription(): string
    {
        return 'Adds the platform-specific index for field value translations.';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($this->indexExists()) {
            return;
        }

        if ($platform instanceof PostgreSQLPlatform) {
            $this->addSql(
                'CREATE INDEX '.self::INDEX_NAME
                .' ON field_value_translation (value)'
            );

            return;
        }

        if (
            $platform instanceof MySQLPlatform
            || $platform instanceof MariaDBPlatform
        ) {
            $this->addSql(
                'CREATE INDEX '.self::INDEX_NAME
                .' ON field_value_translation (value(768))'
            );
        }
    }

    public function down(Schema $schema): void
    {
    }

    /**
     * @throws Exception
     */
    private function indexExists(): bool
    {
        $indexes = $this->connection
            ->createSchemaManager()
            ->introspectTableIndexesByUnquotedName(
                'field_value_translation',
            );

        return isset($indexes[self::INDEX_NAME]);
    }
}
