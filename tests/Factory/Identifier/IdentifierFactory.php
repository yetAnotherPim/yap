<?php

declare(strict_types=1);

namespace App\Tests\Factory\Identifier;

use App\Entity\Identifier\Identifier;
use App\Tests\Factory\Identifier\Type\IdentifierTypeFactory;
use App\Tests\Factory\Product\ProductFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Identifier>
 */
final class IdentifierFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Identifier::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'product' => ProductFactory::new(),
            'type' => IdentifierTypeFactory::new(),
            'value' => self::faker()->text(255),
            'createdAt' => self::faker()->dateTime(),
        ];
    }
}
