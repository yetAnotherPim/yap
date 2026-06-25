<?php

declare(strict_types=1);

namespace App\plugins\SamplePlugin;

use App\PluginManager\Plugin\PluginInterface;
use Doctrine\ORM\EntityManagerInterface;

class Plugin implements PluginInterface
{
    /** return name of plugin, name has to match the plugin's folder */
    public function getName(): string
    {
        return 'SamplePlugin';
    }

    public function getVersion(): string
    {
        return '0.9.0';
    }

    /**
     * Create database schema
     * Use: CREATE TABLE IF NOT EXISTS ...
     * to avoid that the query itself fails.
     */
    public function activate(EntityManagerInterface $entityManager): static
    {
        // TODO: test install integration

        return $this;
    }

    public function deactivate(EntityManagerInterface $entityManager): static
    {
        // TODO: test uninstall

        return $this;
    }

    /**
     * Update database schema
     * Use: version_compare().
     */
    public function update(string $fromVersion, EntityManagerInterface $entityManager): static
    {
        // TODO: test update

        return $this;
    }
}
