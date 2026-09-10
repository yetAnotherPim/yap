<?php

declare(strict_types=1);

namespace App\plugins\Scheduler;

use App\PluginManager\Plugin\PluginInterface;
use Doctrine\ORM\EntityManagerInterface;

class Plugin implements PluginInterface
{
    public function getName(): string
    {
        return 'Scheduler';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function activate(EntityManagerInterface $entityManager): static
    {
        $query = <<<SQL
            CREATE TABLE IF NOT EXISTS yap_scheduler (
            id SMALLINT PRIMARY KEY DEFAULT 1 CHECK (id = 1),
            last_run TIMESTAMP
        );
        SQL;

        $entityManager
            ->getConnection()
            ->executeQuery($query);

        return $this;
    }

    public function deactivate(EntityManagerInterface $entityManager): static
    {
        return $this;
    }

    public function update(string $fromVersion, EntityManagerInterface $entityManager): static
    {
        return $this;
    }
}
