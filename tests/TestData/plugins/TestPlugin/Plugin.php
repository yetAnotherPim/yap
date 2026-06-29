<?php

declare(strict_types=1);

namespace App\Tests\TestData\plugins\TestPlugin;

use App\PluginManager\Plugin\PluginInterface;
use Doctrine\ORM\EntityManagerInterface;

class Plugin implements PluginInterface
{
    /** return name of plugin, name has to match the plugin's folder */
    public function getName(): string
    {
        return 'TestPlugin';
    }

    public function getVersion(): string
    {
        return '0.9.0';
    }

    public function activate(EntityManagerInterface $entityManager): static
    {
        throw new \RuntimeException('Oops');
    }

    public function deactivate(EntityManagerInterface $entityManager): static
    {
        throw new \RuntimeException('Oops');
    }

    public function update(string $fromVersion, EntityManagerInterface $entityManager): static
    {
        throw new \RuntimeException('Oops');
    }
}
