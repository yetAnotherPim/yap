<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260730114004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE field (id UUID NOT NULL, value TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, type_id UUID NOT NULL, product_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX index_product_field_type ON field (type_id)');
        $this->addSql('CREATE INDEX index_product_field_product ON field (product_id)');
        $this->addSql('CREATE INDEX index_product_field_value ON field (value)');
        $this->addSql('CREATE UNIQUE INDEX unique_product_field_product_type ON field (type_id, product_id)');
        $this->addSql('CREATE TABLE field_type (id UUID NOT NULL, code VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, translatable BOOLEAN NOT NULL, filterable BOOLEAN NOT NULL, input_type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX unique_product_field_type_code ON field_type (code)');
        $this->addSql('CREATE TABLE field_type_title_translation (id UUID NOT NULL, value TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, locale_id VARCHAR(8) NOT NULL, field_type_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX index_product_field_type_translation_fieldtype_id ON field_type_title_translation (field_type_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_product_field_type_translation_locale_fieldtype ON field_type_title_translation (locale_id)');
        $this->addSql('CREATE TABLE field_value_translation (id UUID NOT NULL, value TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, locale_id VARCHAR(8) NOT NULL, field_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX index_product_field_translation_value ON field_value_translation (value)');
        $this->addSql('CREATE INDEX index_product_field_translation_locale ON field_value_translation (locale_id)');
        $this->addSql('CREATE INDEX index_product_field_translation_field ON field_value_translation (field_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_product_field_translation_locale_field ON field_value_translation (locale_id, field_id)');
        $this->addSql('CREATE TABLE identifier (id UUID NOT NULL, value VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, type_id UUID NOT NULL, product_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX index_product_identifier_type ON identifier (type_id)');
        $this->addSql('CREATE INDEX index_product_intendifier_product ON identifier (product_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_product_identifier_product_type ON identifier (type_id, product_id)');
        $this->addSql('CREATE TABLE identifier_type (id UUID NOT NULL, title VARCHAR(255) NOT NULL, data_type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX unique_product_identifier_type_title ON identifier_type (title)');
        $this->addSql('CREATE TABLE locale (id VARCHAR(8) NOT NULL, is_default BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE plugin (id UUID NOT NULL, name VARCHAR(255) NOT NULL, namespace VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, installed_version VARCHAR(255) DEFAULT NULL, version VARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX unique_plugin_name ON plugin (name)');
        $this->addSql('CREATE UNIQUE INDEX unique_plugin_namespace ON plugin (namespace)');
        $this->addSql('CREATE TABLE product (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, product_type_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX index_product_producttype ON product (product_type_id)');
        $this->addSql('CREATE TABLE product_type (id UUID NOT NULL, code VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX unique_product_type_code ON product_type (code)');
        $this->addSql('CREATE TABLE product_type_field_configuration (id UUID NOT NULL, position INT NOT NULL, default_value TEXT DEFAULT NULL, product_type_id UUID NOT NULL, field_type_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX index_product_type_field_configuration_field_type ON product_type_field_configuration (field_type_id)');
        $this->addSql('CREATE INDEX index_product_type_field_configuration_product_type ON product_type_field_configuration (product_type_id)');
        $this->addSql('CREATE TABLE product_type_title_translation (id UUID NOT NULL, value TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, locale_id VARCHAR(8) NOT NULL, product_type_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX index_product_type_translation_value ON product_type_title_translation (value)');
        $this->addSql('CREATE INDEX index_product_type_translation_producttype ON product_type_title_translation (product_type_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_product_type_translation_locale_producttype ON product_type_title_translation (locale_id)');
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, username VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "user" (username)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
        $this->addSql('ALTER TABLE field ADD CONSTRAINT FK_5BF54558C54C8C93 FOREIGN KEY (type_id) REFERENCES field_type (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE field ADD CONSTRAINT FK_5BF545584584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE field_type_title_translation ADD CONSTRAINT FK_736B57BEE559DFD1 FOREIGN KEY (locale_id) REFERENCES locale (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE field_type_title_translation ADD CONSTRAINT FK_736B57BE2B68A933 FOREIGN KEY (field_type_id) REFERENCES field_type (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE field_value_translation ADD CONSTRAINT FK_39018197E559DFD1 FOREIGN KEY (locale_id) REFERENCES locale (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE field_value_translation ADD CONSTRAINT FK_39018197443707B0 FOREIGN KEY (field_id) REFERENCES field (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE identifier ADD CONSTRAINT FK_772E836AC54C8C93 FOREIGN KEY (type_id) REFERENCES identifier_type (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE identifier ADD CONSTRAINT FK_772E836A4584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD14959723 FOREIGN KEY (product_type_id) REFERENCES product_type (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_type_field_configuration ADD CONSTRAINT FK_FDED18214959723 FOREIGN KEY (product_type_id) REFERENCES product_type (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_type_field_configuration ADD CONSTRAINT FK_FDED1822B68A933 FOREIGN KEY (field_type_id) REFERENCES field_type (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_type_title_translation ADD CONSTRAINT FK_6C4B495DE559DFD1 FOREIGN KEY (locale_id) REFERENCES locale (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_type_title_translation ADD CONSTRAINT FK_6C4B495D14959723 FOREIGN KEY (product_type_id) REFERENCES product_type (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE field DROP CONSTRAINT FK_5BF54558C54C8C93');
        $this->addSql('ALTER TABLE field DROP CONSTRAINT FK_5BF545584584665A');
        $this->addSql('ALTER TABLE field_type_title_translation DROP CONSTRAINT FK_736B57BEE559DFD1');
        $this->addSql('ALTER TABLE field_type_title_translation DROP CONSTRAINT FK_736B57BE2B68A933');
        $this->addSql('ALTER TABLE field_value_translation DROP CONSTRAINT FK_39018197E559DFD1');
        $this->addSql('ALTER TABLE field_value_translation DROP CONSTRAINT FK_39018197443707B0');
        $this->addSql('ALTER TABLE identifier DROP CONSTRAINT FK_772E836AC54C8C93');
        $this->addSql('ALTER TABLE identifier DROP CONSTRAINT FK_772E836A4584665A');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04AD14959723');
        $this->addSql('ALTER TABLE product_type_field_configuration DROP CONSTRAINT FK_FDED18214959723');
        $this->addSql('ALTER TABLE product_type_field_configuration DROP CONSTRAINT FK_FDED1822B68A933');
        $this->addSql('ALTER TABLE product_type_title_translation DROP CONSTRAINT FK_6C4B495DE559DFD1');
        $this->addSql('ALTER TABLE product_type_title_translation DROP CONSTRAINT FK_6C4B495D14959723');
        $this->addSql('DROP TABLE field');
        $this->addSql('DROP TABLE field_type');
        $this->addSql('DROP TABLE field_type_title_translation');
        $this->addSql('DROP TABLE field_value_translation');
        $this->addSql('DROP TABLE identifier');
        $this->addSql('DROP TABLE identifier_type');
        $this->addSql('DROP TABLE locale');
        $this->addSql('DROP TABLE plugin');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_type');
        $this->addSql('DROP TABLE product_type_field_configuration');
        $this->addSql('DROP TABLE product_type_title_translation');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
