<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261102133146 extends AbstractMigration
{
    private const INDEX_NAME = 'index_product_field_value';

    public function getDescription(): string
    {
        return 'Adds the platform-specific index for product field values.';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof PostgreSQLPlatform) {
            $this->addSql(
                'CREATE INDEX IF NOT EXISTS '.self::INDEX_NAME
                .' ON field (value)',
            );

            return;
        }

        if (
            $platform instanceof MySQLPlatform
            || $platform instanceof MariaDBPlatform
        ) {
            $this->addSql(
                'CREATE INDEX '.self::INDEX_NAME
                .' ON field (value(768))',
            );
        }
    }
}
