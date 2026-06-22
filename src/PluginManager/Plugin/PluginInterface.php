<?php

declare(strict_types=1);

namespace App\PluginManager\Plugin;

interface PluginInterface
{
    public function getName(): string;

    public function getVersion(): string;

    public function activate(): static;

    public function deactivate(): static;

    public function update(string $fromVersion): static;
}
