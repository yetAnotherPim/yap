<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260617112629 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE field (id UUID NOT NULL, value TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, type_id UUID NOT NULL, product_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX index_field_type_id ON field (type_id)');
        $this->addSql('CREATE INDEX index_field_product_id ON field (product_id)');
        $this->addSql('CREATE INDEX index_field_value ON field (value)');
        $this->addSql('CREATE UNIQUE INDEX field_product_type ON field (type_id, product_id)');
        $this->addSql('ALTER TABLE field ADD CONSTRAINT FK_5BF54558C54C8C93 FOREIGN KEY (type_id) REFERENCES field_type (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE field ADD CONSTRAINT FK_5BF545584584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE');
        $this->addSql('ALTER INDEX index_type_id RENAME TO index_identifier_type_id');
        $this->addSql('ALTER INDEX index_product_id RENAME TO index_intendifier_product_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE field DROP CONSTRAINT FK_5BF54558C54C8C93');
        $this->addSql('ALTER TABLE field DROP CONSTRAINT FK_5BF545584584665A');
        $this->addSql('DROP TABLE field');
        $this->addSql('ALTER INDEX index_identifier_type_id RENAME TO index_type_id');
        $this->addSql('ALTER INDEX index_intendifier_product_id RENAME TO index_product_id');
    }
}
