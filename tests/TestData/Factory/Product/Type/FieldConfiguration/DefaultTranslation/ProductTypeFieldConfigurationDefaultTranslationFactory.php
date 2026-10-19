<?php

declare(strict_types=1);

namespace App\Tests\TestData\Factory\Product\Type\FieldConfiguration\DefaultTranslation;

use App\Entity\Product\Type\FieldConfiguration\DefaultTranslation\ProductTypeFieldConfigurationDefaultTranslation;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ProductTypeFieldConfigurationDefaultTranslation>
 */
final class ProductTypeFieldConfigurationDefaultTranslationFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return ProductTypeFieldConfigurationDefaultTranslation::class;
    }

    #[\Override]
    protected function defaults(): array
    {
        return [
            'locale' => null, // TODO add App\Entity\Locale\Locale type manually
            'productTypeFieldConfiguration' => null, // TODO add App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration type manually
        ];
    }
}
