<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260828110356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX unique_product_field_type_translation_locale_fieldtype');
        $this->addSql('CREATE INDEX IDX_736B57BEE559DFD1 ON field_type_title_translation (locale_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_product_field_type_translation_locale_fieldtype ON field_type_title_translation (locale_id, field_type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_736B57BEE559DFD1');
        $this->addSql('DROP INDEX unique_product_field_type_translation_locale_fieldtype');
        $this->addSql('CREATE UNIQUE INDEX unique_product_field_type_translation_locale_fieldtype ON field_type_title_translation (locale_id)');
    }
}
