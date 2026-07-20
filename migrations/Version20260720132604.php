<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260720132604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_type_field_configuration (id UUID NOT NULL, position INT NOT NULL, default_value JSON DEFAULT NULL, product_type_id UUID NOT NULL, field_type_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX index_product_type_field_configuration_field_type ON product_type_field_configuration (field_type_id)');
        $this->addSql('CREATE INDEX index_product_type_field_configuration_product_type ON product_type_field_configuration (product_type_id)');
        $this->addSql('ALTER TABLE product_type_field_configuration ADD CONSTRAINT FK_FDED18214959723 FOREIGN KEY (product_type_id) REFERENCES product_type (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_type_field_configuration ADD CONSTRAINT FK_FDED1822B68A933 FOREIGN KEY (field_type_id) REFERENCES field_type (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_type_field_configuration DROP CONSTRAINT FK_FDED18214959723');
        $this->addSql('ALTER TABLE product_type_field_configuration DROP CONSTRAINT FK_FDED1822B68A933');
        $this->addSql('DROP TABLE product_type_field_configuration');
    }
}
