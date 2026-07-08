<?php

declare(strict_types=1);

namespace App\Tests\TestData\Factory\Product;

use App\Entity\Product\Product;
use App\Tests\TestData\Factory\Product\Type\ProductTypeFactory;
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
            'productType' => ProductTypeFactory::new(),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }
}
