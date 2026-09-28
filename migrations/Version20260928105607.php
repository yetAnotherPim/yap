<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260928105607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_column_configuration (id UUID NOT NULL, position INT NOT NULL, user_id UUID NOT NULL, field_type_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX index_product_column_configuration_user ON product_column_configuration (user_id)');
        $this->addSql('CREATE INDEX index_product_column_configuration_field_type ON product_column_configuration (field_type_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_product_column_configuration_user_field_type ON product_column_configuration (user_id, field_type_id)');
        $this->addSql('ALTER TABLE product_column_configuration ADD CONSTRAINT FK_965404F8A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_column_configuration ADD CONSTRAINT FK_965404F82B68A933 FOREIGN KEY (field_type_id) REFERENCES field_type (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE yap_scheduler (id SMALLINT DEFAULT 1 NOT NULL, last_run TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE product_column_configuration DROP CONSTRAINT FK_965404F8A76ED395');
        $this->addSql('ALTER TABLE product_column_configuration DROP CONSTRAINT FK_965404F82B68A933');
    }
}
