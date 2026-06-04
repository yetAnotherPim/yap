<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260604125316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add identifier';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE identifier (id UUID NOT NULL, value VARCHAR(255) NOT NULL, type_id UUID NOT NULL, product_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX index_type_id ON identifier (type_id)');
        $this->addSql('CREATE INDEX index_product_id ON identifier (product_id)');
        $this->addSql('CREATE UNIQUE INDEX identifier_product_type ON identifier (type_id, product_id)');
        $this->addSql('ALTER TABLE identifier ADD CONSTRAINT FK_772E836AC54C8C93 FOREIGN KEY (type_id) REFERENCES identifier_type (id) NOT DEFERRABLE');
        $this->addSql('ALTER TABLE identifier ADD CONSTRAINT FK_772E836A4584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE identifier DROP CONSTRAINT FK_772E836AC54C8C93');
        $this->addSql('ALTER TABLE identifier DROP CONSTRAINT FK_772E836A4584665A');
        $this->addSql('DROP TABLE identifier');
    }
}
