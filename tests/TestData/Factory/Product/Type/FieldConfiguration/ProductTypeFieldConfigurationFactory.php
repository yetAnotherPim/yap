<?php

declare(strict_types=1);

namespace App\Tests\TestData\Factory\Product\Type\FieldConfiguration;

use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ProductTypeFieldConfiguration>
 */
final class ProductTypeFieldConfigurationFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return ProductTypeFieldConfiguration::class;
    }

    #[\Override]
    protected function defaults(): array
    {
        return [
            'fieldType' => null, // TODO add App\Entity\Product\Field\Type\FieldType type manually
            'position' => self::faker()->randomNumber(),
            'productType' => null, // TODO add App\Entity\Product\Type\ProductType type manually
            'required' => self::faker()->boolean(),
        ];
    }
}
