<?php

declare(strict_types=1);

namespace App\PluginManager\Plugin;

use Doctrine\ORM\EntityManagerInterface;

interface PluginInterface
{
    public function getName(): string;

    public function getVersion(): string;

    public function activate(EntityManagerInterface $entityManager): static;

    public function deactivate(EntityManagerInterface $entityManager): static;

    public function update(string $fromVersion, EntityManagerInterface $entityManager): static;
}
