<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260828110519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX unique_product_type_translation_locale_producttype');
        $this->addSql('CREATE INDEX IDX_6C4B495DE559DFD1 ON product_type_title_translation (locale_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_product_type_translation_locale_producttype ON product_type_title_translation (locale_id, product_type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_6C4B495DE559DFD1');
        $this->addSql('DROP INDEX unique_product_type_translation_locale_producttype');
        $this->addSql('CREATE UNIQUE INDEX unique_product_type_translation_locale_producttype ON product_type_title_translation (locale_id)');
    }
}
