<?php

declare(strict_types=1);

namespace App\Tests\TestData\Factory\Product\Type;

use App\Entity\Product\Type\ProductType;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ProductType>
 */
final class ProductTypeFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return ProductType::class;
    }

    #[\Override]
    protected function defaults(): array
    {
        return [
            'code' => self::faker()->text(255),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'title' => self::faker()->text(255),
        ];
    }
}
