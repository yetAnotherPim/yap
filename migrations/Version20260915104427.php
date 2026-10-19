<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260915104427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_type_field_configuration_default_translation (id UUID NOT NULL, value TEXT DEFAULT NULL, locale_id VARCHAR(8) NOT NULL, product_type_field_configuration_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX index_default_translation_locale_id ON product_type_field_configuration_default_translation (locale_id)');
        $this->addSql('CREATE INDEX index_default_translation_product_type_field_configuration_id ON product_type_field_configuration_default_translation (product_type_field_configuration_id)');
        $this->addSql('ALTER TABLE product_type_field_configuration_default_translation ADD CONSTRAINT FK_4B8A2D06E559DFD1 FOREIGN KEY (locale_id) REFERENCES locale (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_type_field_configuration_default_translation ADD CONSTRAINT FK_4B8A2D067DB8015C FOREIGN KEY (product_type_field_configuration_id) REFERENCES product_type_field_configuration (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_type_field_configuration_default_translation DROP CONSTRAINT FK_4B8A2D06E559DFD1');
        $this->addSql('ALTER TABLE product_type_field_configuration_default_translation DROP CONSTRAINT FK_4B8A2D067DB8015C');
        $this->addSql('DROP TABLE product_type_field_configuration_default_translation');
    }
}
