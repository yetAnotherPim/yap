<?php

declare(strict_types=1);

namespace App\Tests\TestData\Factory\Plugin;

use App\Entity\Plugin\Plugin;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Plugin>
 */
final class PluginFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Plugin::class;
    }

    #[\Override]
    protected function defaults(): array
    {
        return [
            'active' => self::faker()->boolean(),
            'name' => self::faker()->text(255),
            'namespace' => self::faker()->text(255),
        ];
    }
}
