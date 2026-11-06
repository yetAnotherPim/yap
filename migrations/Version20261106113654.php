<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261106113654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Limits field type title translation values to 255 characters.';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof PostgreSQLPlatform) {
            $this->addSql(
                'ALTER TABLE field_type_title_translation
             ALTER COLUMN value TYPE VARCHAR(255)
             USING LEFT(value, 255)',
            );

            return;
        }

        if ($platform instanceof MySQLPlatform || $platform instanceof MariaDBPlatform) {
            $this->addSql(
                'UPDATE field_type_title_translation
                 SET value = LEFT(value, 255)
                 WHERE CHAR_LENGTH(value) > 255',
            );

            $this->addSql(
                'ALTER TABLE field_type_title_translation
                 MODIFY value VARCHAR(255) DEFAULT NULL',
            );
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
