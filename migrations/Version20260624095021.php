<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260624095021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX plugin_namespace');
        $this->addSql('ALTER TABLE plugin RENAME COLUMN namespace TO path');
        $this->addSql('CREATE UNIQUE INDEX plugin_path ON plugin (path)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX plugin_path');
        $this->addSql('ALTER TABLE plugin RENAME COLUMN path TO namespace');
        $this->addSql('CREATE UNIQUE INDEX plugin_namespace ON plugin (namespace)');
    }
}
