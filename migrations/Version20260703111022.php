<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260703111022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE field_type_title_translation ALTER locale_id TYPE VARCHAR(8)');
        $this->addSql('ALTER TABLE field_value_translation ALTER locale_id TYPE VARCHAR(8)');
        $this->addSql('ALTER TABLE locale ALTER id TYPE VARCHAR(8)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE field_type_title_translation ALTER locale_id TYPE VARCHAR(5)');
        $this->addSql('ALTER TABLE field_value_translation ALTER locale_id TYPE VARCHAR(5)');
        $this->addSql('ALTER TABLE locale ALTER id TYPE VARCHAR(5)');
    }
}
