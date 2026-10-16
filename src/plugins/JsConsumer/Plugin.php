<?php

declare(strict_types=1);

namespace App\plugins\JsConsumer;

use App\PluginManager\Plugin\PluginInterface;
use App\PluginManager\Plugin\PluginWithAssetsInterface;
use Doctrine\ORM\EntityManagerInterface;

class Plugin implements PluginWithAssetsInterface
{
    public function getName(): string
    {
        return 'JsConsumer';
    }

    public function getVersion(): string
    {
        return '1.0.0';
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
