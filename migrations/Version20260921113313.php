<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260921113313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE field_type_title_translation DROP CONSTRAINT fk_736b57bee559dfd1');
        $this->addSql('ALTER TABLE field_type_title_translation ADD CONSTRAINT FK_736B57BEE559DFD1 FOREIGN KEY (locale_id) REFERENCES locale (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE field_value_translation DROP CONSTRAINT fk_39018197e559dfd1');
        $this->addSql('ALTER TABLE field_value_translation ADD CONSTRAINT FK_39018197E559DFD1 FOREIGN KEY (locale_id) REFERENCES locale (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_type_field_configuration_default_translation DROP CONSTRAINT fk_4b8a2d06e559dfd1');
        $this->addSql('ALTER TABLE product_type_field_configuration_default_translation ADD CONSTRAINT FK_4B8A2D06E559DFD1 FOREIGN KEY (locale_id) REFERENCES locale (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_type_title_translation DROP CONSTRAINT fk_6c4b495de559dfd1');
        $this->addSql('ALTER TABLE product_type_title_translation ADD CONSTRAINT FK_6C4B495DE559DFD1 FOREIGN KEY (locale_id) REFERENCES locale (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE field_type_title_translation DROP CONSTRAINT FK_736B57BEE559DFD1');
        $this->addSql('ALTER TABLE field_type_title_translation ADD CONSTRAINT fk_736b57bee559dfd1 FOREIGN KEY (locale_id) REFERENCES locale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE field_value_translation DROP CONSTRAINT FK_39018197E559DFD1');
        $this->addSql('ALTER TABLE field_value_translation ADD CONSTRAINT fk_39018197e559dfd1 FOREIGN KEY (locale_id) REFERENCES locale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_type_field_configuration_default_translation DROP CONSTRAINT FK_4B8A2D06E559DFD1');
        $this->addSql('ALTER TABLE product_type_field_configuration_default_translation ADD CONSTRAINT fk_4b8a2d06e559dfd1 FOREIGN KEY (locale_id) REFERENCES locale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_type_title_translation DROP CONSTRAINT FK_6C4B495DE559DFD1');
        $this->addSql('ALTER TABLE product_type_title_translation ADD CONSTRAINT fk_6c4b495de559dfd1 FOREIGN KEY (locale_id) REFERENCES locale (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
