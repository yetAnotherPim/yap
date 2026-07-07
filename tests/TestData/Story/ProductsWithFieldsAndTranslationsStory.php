<?php

declare(strict_types=1);

namespace App\Tests\TestData\Story;

use App\Tests\TestData\Factory\Locale\LocaleFactory;
use App\Tests\TestData\Factory\Product\Field\FieldFactory;
use App\Tests\TestData\Factory\Product\Field\Translation\FieldValueTranslationFactory;
use App\Tests\TestData\Factory\Product\Field\Type\FieldTypeFactory;
use App\Tests\TestData\Factory\Product\ProductFactory;
use Zenstruck\Foundry\Story;

final class ProductsWithFieldsAndTranslationsStory extends Story
{
    public function build(): void
    {
        $localeDE = LocaleFactory::createOne(['id' => 'de_DE']);
        $localeES = LocaleFactory::createOne(['id' => 'es_ES']);

        $fieldType = FieldTypeFactory::createOne([
            'code' => 'product_name',
            'title' => 'Product name',
        ]);

        $fieldType2 = FieldTypeFactory::createOne([
            'code' => 'similar_to',
            'title' => 'Similar to product',
        ]);

        ProductFactory::createOne(
            [
                'fields' => [
                    FieldFactory::new([
                        'type' => $fieldType,
                        'value' => 'Product1',
                        'translations' => [
                            FieldValueTranslationFactory::new([
                                'locale' => $localeDE,
                                'value' => 'Produkt1',
                            ]),
                            FieldValueTranslationFactory::new([
                                'locale' => $localeES,
                                'value' => 'Producto1',
                            ]),
                        ],
                    ]),
                    FieldFactory::new([
                        'type' => $fieldType2,
                        'value' => null,
                    ]),
                ],
            ]
        );

        ProductFactory::createOne(
            [
                'fields' => [
                    FieldFactory::new([
                        'type' => $fieldType,
                        'value' => 'Product2',
                        'translations' => [
                            FieldValueTranslationFactory::new([
                                'locale' => $localeDE,
                                'value' => 'Produkt2',
                            ]),
                            FieldValueTranslationFactory::new([
                                'locale' => $localeES,
                                'value' => 'Producto2',
                            ]),
                        ],
                    ]),
                    FieldFactory::new([
                        'type' => $fieldType2,
                        'value' => 'Product1',
                    ]),
                ],
            ]
        );
    }
}
