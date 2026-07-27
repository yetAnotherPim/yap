<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260727115310 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, username VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (username)');
        $this->addSql('ALTER INDEX index_product_field_product_type RENAME TO unique_product_field_product_type');
        $this->addSql('ALTER INDEX index_product_field_type_code RENAME TO unique_product_field_type_code');
        $this->addSql('ALTER INDEX index_product_field_type_translation_locale_fieldtype RENAME TO unique_product_field_type_translation_locale_fieldtype');
        $this->addSql('ALTER INDEX index_product_field_translation_locale_field RENAME TO unique_product_field_translation_locale_field');
        $this->addSql('ALTER INDEX index_product_identifier_product_type RENAME TO unique_product_identifier_product_type');
        $this->addSql('ALTER INDEX index_product_identifier_type_title RENAME TO unique_product_identifier_type_title');
        $this->addSql('ALTER INDEX index_plugin_name RENAME TO unique_plugin_name');
        $this->addSql('ALTER INDEX index_plugin_namespace RENAME TO unique_plugin_namespace');
        $this->addSql('ALTER INDEX index_product_type_code RENAME TO unique_product_type_code');
        $this->addSql('ALTER INDEX index_product_type_translation_locale_producttype RENAME TO unique_product_type_translation_locale_producttype');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE "user"');
        $this->addSql('ALTER INDEX unique_product_field_product_type RENAME TO index_product_field_product_type');
        $this->addSql('ALTER INDEX unique_product_field_type_code RENAME TO index_product_field_type_code');
        $this->addSql('ALTER INDEX unique_product_field_type_translation_locale_fieldtype RENAME TO index_product_field_type_translation_locale_fieldtype');
        $this->addSql('ALTER INDEX unique_product_field_translation_locale_field RENAME TO index_product_field_translation_locale_field');
        $this->addSql('ALTER INDEX unique_product_identifier_product_type RENAME TO index_product_identifier_product_type');
        $this->addSql('ALTER INDEX unique_product_identifier_type_title RENAME TO index_product_identifier_type_title');
        $this->addSql('ALTER INDEX unique_plugin_name RENAME TO index_plugin_name');
        $this->addSql('ALTER INDEX unique_plugin_namespace RENAME TO index_plugin_namespace');
        $this->addSql('ALTER INDEX unique_product_type_code RENAME TO index_product_type_code');
        $this->addSql('ALTER INDEX unique_product_type_translation_locale_producttype RENAME TO index_product_type_translation_locale_producttype');
    }
}
