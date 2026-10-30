<?php

declare(strict_types=1);

namespace migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260820110214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add locales';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'ar\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'bg_BG\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'ca_AD\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'cs_CZ\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'da_DK\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'de_DE\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'el_GR\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'en_GB\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'es_ES\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'eu_ES\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'fa_IR\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'fi_FI\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'fr_FR\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'gl_ES\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'he_IL\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'hr_HR\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'hu_HU\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'hy_AM\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'id_ID\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'it_IT\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'lb_LU\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'lt_LT\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'mk_MK\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'nl_NL\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'no_NO\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'pl_PL\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'pt_PT\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'pt_BR\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'ro_RO\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'ru_UA\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'sk_SK\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'sl_SI\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'sr_RS\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'sv_SE\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'tr_TR\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'uk_UA\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'zh_CN\', false) ON CONFLICT (id) DO NOTHING;');
        $this->addSql('INSERT INTO "locale" (id, is_default) VALUES (\'zh_TW\', false) ON CONFLICT (id) DO NOTHING;');

        $this->addSql(
            'UPDATE "locale"
                 SET is_default = true
                 WHERE id = \'en_GB\'
                   AND NOT EXISTS (
                       SELECT 1
                       FROM "locale"
                       WHERE is_default = false
                   );'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
