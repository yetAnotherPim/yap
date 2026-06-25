<?php

declare(strict_types=1);

namespace App\plugins\Test;

use App\PluginManager\Plugin\PluginInterface;
use Doctrine\ORM\EntityManagerInterface;

class Plugin implements PluginInterface
{
    public function getName(): string
    {
        return 'Test';
    }

    public function getVersion(): string
    {
        return '0.9.1';
    }

    public function activate(EntityManagerInterface $entityManager): static
    {
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
