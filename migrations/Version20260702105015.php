<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260702105015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE field_type_title_translation (id UUID NOT NULL, value TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, locale_id VARCHAR(5) NOT NULL, field_type_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_736B57BE2B68A933 ON field_type_title_translation (field_type_id)');
        $this->addSql('CREATE UNIQUE INDEX fieldtype_translation_locale_fieldtype ON field_type_title_translation (locale_id)');
        $this->addSql('CREATE TABLE field_value_translation (id UUID NOT NULL, value TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, locale_id VARCHAR(5) NOT NULL, field_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_39018197E559DFD1 ON field_value_translation (locale_id)');
        $this->addSql('CREATE INDEX IDX_39018197443707B0 ON field_value_translation (field_id)');
        $this->addSql('CREATE UNIQUE INDEX field_translation_locale_field ON field_value_translation (locale_id, field_id)');
        $this->addSql('ALTER TABLE field_type_title_translation ADD CONSTRAINT FK_736B57BEE559DFD1 FOREIGN KEY (locale_id) REFERENCES locale (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE field_type_title_translation ADD CONSTRAINT FK_736B57BE2B68A933 FOREIGN KEY (field_type_id) REFERENCES field_type (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE field_value_translation ADD CONSTRAINT FK_39018197E559DFD1 FOREIGN KEY (locale_id) REFERENCES locale (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE field_value_translation ADD CONSTRAINT FK_39018197443707B0 FOREIGN KEY (field_id) REFERENCES field (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE field_type_title_translation DROP CONSTRAINT FK_736B57BEE559DFD1');
        $this->addSql('ALTER TABLE field_type_title_translation DROP CONSTRAINT FK_736B57BE2B68A933');
        $this->addSql('ALTER TABLE field_value_translation DROP CONSTRAINT FK_39018197E559DFD1');
        $this->addSql('ALTER TABLE field_value_translation DROP CONSTRAINT FK_39018197443707B0');
        $this->addSql('DROP TABLE field_type_title_translation');
        $this->addSql('DROP TABLE field_value_translation');
    }
}
