<?php

declare(strict_types=1);

namespace App\Tests\Factory\Product;

use App\Entity\Product\Product;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Product>
 */
final class ProductFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Product::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }
}
