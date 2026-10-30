<?php

declare(strict_types=1);

namespace DDoctrineMigrations\Data;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260820110215 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create default admin user';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO "user" (id, username, email, first_name, last_name, roles, password) VALUES (\'019fa37e-f23b-7125-858b-09fc87ca2951\', \'admin\', \'admin@yap.com\', \'Yet Another\', \'Pim\', \'[]\', \'$2y$13$S7Hz4gW2KuX4GpA8c4Ixz.1CK8VKkHt19AEg3w9fSZjr8DRF57DOa\') ON CONFLICT (id) DO NOTHING;');
    }

    public function down(Schema $schema): void
    {
    }
}
