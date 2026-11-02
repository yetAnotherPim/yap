<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261102134510 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Limits product type title translation values to 255 characters on PostgreSQL.';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if (!$platform instanceof PostgreSQLPlatform) {
            return;
        }

        $this->addSql(
            'ALTER TABLE product_type_title_translation
             ALTER COLUMN value TYPE VARCHAR(255)
             USING LEFT(value, 255)',
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
