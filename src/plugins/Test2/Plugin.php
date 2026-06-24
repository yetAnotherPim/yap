<?php

declare(strict_types=1);

namespace App\plugins\Test2;

use App\PluginManager\Plugin\PluginInterface;

class Plugin implements PluginInterface
{
    public function getName(): string
    {
        return 'Test2';
    }

    public function getVersion(): string
    {
        return '0.9.0';
    }

    public function activate(): static
    {
        // TODO: provide em
        // TODO: test install integration

        return $this;
    }

    public function deactivate(): static
    {
        // TODO: provide em
        // TODO: test uninstall

        return $this;
    }

    public function update(string $fromVersion): static
    {
        // TODO: provide em
        // TODO: provide fromVersion
        // TODO: test update

        return $this;
    }
}
