<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260820110214 extends AbstractMigration
{
    private const array LOCALES = [
        'ar',
        'bg_BG',
        'ca_AD',
        'cs_CZ',
        'da_DK',
        'de_DE',
        'el_GR',
        'en_GB',
        'es_ES',
        'eu_ES',
        'fa_IR',
        'fi_FI',
        'fr_FR',
        'gl_ES',
        'he_IL',
        'hr_HR',
        'hu_HU',
        'hy_AM',
        'id_ID',
        'it_IT',
        'lb_LU',
        'lt_LT',
        'mk_MK',
        'nl_NL',
        'no_NO',
        'pl_PL',
        'pt_PT',
        'pt_BR',
        'ro_RO',
        'ru_UA',
        'sk_SK',
        'sl_SI',
        'sr_RS',
        'sv_SE',
        'tr_TR',
        'uk_UA',
        'zh_CN',
        'zh_TW',
    ];

    private const string DEFAULT_LOCALE = 'en_GB';

    public function getDescription(): string
    {
        return 'Add locales';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform();

        if ($platform instanceof PostgreSQLPlatform) {
            $insertSql = <<<'SQL'
                INSERT INTO locale (id, is_default)
                VALUES (?, false)
                ON CONFLICT (id) DO NOTHING
            SQL;
        } elseif ($platform instanceof AbstractMySQLPlatform) {
            $insertSql = <<<'SQL'
                INSERT INTO locale (id, is_default)
                VALUES (?, false)
                ON DUPLICATE KEY UPDATE id = id
            SQL;
        } else {
            throw new \RuntimeException(\sprintf('Unsupported database platform: %s', $platform::class));
        }

        foreach (self::LOCALES as $locale) {
            $this->addSql($insertSql, [$locale]);
        }

        $connection = $this->connection;

        $hasDefaultLocale = (bool) $connection->fetchOne(
            'SELECT EXISTS (
                       SELECT 1
                       FROM locale
                       WHERE is_default = true
               )',
        );

        if (!$hasDefaultLocale) {
            $this->addSql(
                'UPDATE locale
                     SET is_default = true
                     WHERE id = ?',
                [self::DEFAULT_LOCALE],
            );
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
