<?php

declare(strict_types=1);

namespace App\DemoData\Definition;

/**
 * @phpstan-type ProductVariationField array{
 *     options: non-empty-list<string>
 * }
 * @phpstan-type ProductTypeDefinition array{
 *     title: array<string, string>,
 *     fields: array<string, array{
 *         required?: bool,
 *         options?: list<string>
 *     }>
 * }
 */
class ProductTypeDefinitions
{
    /**
     * @var array<string, ProductTypeDefinition>&array{
     *     apparel: array{
     *         title: array<string, string>,
     *         fields: array<string, array{
     *             required?: bool,
     *             options?: list<string>
     *         }> & array{
     *             colour: ProductVariationField,
     *             size: ProductVariationField,
     *             fit: ProductVariationField
     *         }
     *     }
     * }
     */
    public const array PRODUCT_TYPES = [
        'apparel' => [
            'title' => [
                'en_GB' => 'Apparel',
                'de_DE' => 'Bekleidung',
                'fr_FR' => 'Vêtements',
                'es_ES' => 'Ropa',
                'it_IT' => 'Abbigliamento',
            ],
            'fields' => [
                'section_general' => [],
                'name' => ['required' => true],
                'description' => [],

                'section_attributes' => [],
                'material' => [],
                'colour' => [
                    'options' => [
                        'Black',
                        'White',
                        'Navy',
                        'Grey',
                        'Red',
                        'Green',
                    ],
                ],
                'size' => [
                    'options' => ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
                ],
                'fit' => [
                    'options' => [
                        'Slim fit',
                        'Regular fit',
                        'Relaxed fit',
                        'Oversized',
                    ],
                ],
                'care_instructions' => [
                    'options' => [
                        'Machine wash 30°C',
                        'Machine wash 40°C',
                        'Hand wash',
                        'Do not bleach',
                        'Do not tumble dry',
                        'Iron at low temperature',
                    ],
                ],

                'section_marketing' => [],
                'marketplace_enabled' => [],
                'marketplace_title' => [],
                'marketplace_description' => [],
                'marketplace_category' => [
                    'options' => [
                        'Clothing',
                        'Accessories',
                        'Footwear',
                    ],
                ],

                'section_media' => [],
                'product_image' => [],
                'product_datasheet' => [],
            ],
        ],

        'electronics' => [
            'title' => [
                'en_GB' => 'Electronics',
                'de_DE' => 'Elektronik',
                'fr_FR' => 'Électronique',
                'es_ES' => 'Electrónica',
                'it_IT' => 'Elettronica',
            ],
            'fields' => [
                'section_general' => [],
                'name' => ['required' => true],
                'description' => [],

                'section_attributes' => [],
                'manufacturer' => [],
                'model' => [],
                'voltage' => [],
                'power' => [],
                'connectivity' => [
                    'options' => [
                        'Bluetooth',
                        'Wi-Fi',
                        'USB-C',
                        'Ethernet',
                        'NFC',
                    ],
                ],

                'section_marketing' => [],
                'marketplace_enabled' => [],
                'marketplace_title' => [],
                'marketplace_description' => [],
                'marketplace_category' => [
                    'options' => [
                        'Audio',
                        'Computers',
                        'Accessories',
                        'Smart Home',
                    ],
                ],

                'section_media' => [],
                'product_image' => [],
                'product_datasheet' => [],
            ],
        ],

        'furniture' => [
            'title' => [
                'en_GB' => 'Furniture',
                'de_DE' => 'Möbel',
                'fr_FR' => 'Mobilier',
                'es_ES' => 'Muebles',
                'it_IT' => 'Arredamento',
            ],
            'fields' => [
                'section_general' => [],
                'name' => ['required' => true],
                'description' => [],

                'section_attributes' => [],
                'material' => [],
                'width' => [],
                'height' => [],
                'depth' => [],
                'weight' => [],
                'assembly_required' => [],

                'section_marketing' => [],
                'marketplace_enabled' => [],
                'marketplace_title' => [],
                'marketplace_description' => [],
                'marketplace_category' => [
                    'options' => [
                        'Tables',
                        'Chairs',
                        'Storage',
                        'Lighting',
                        'Beds',
                    ],
                ],

                'section_media' => [],
                'product_image' => [],
                'product_datasheet' => [],
            ],
        ],

        'cosmetics' => [
            'title' => [
                'en_GB' => 'Cosmetics',
                'de_DE' => 'Kosmetik',
                'fr_FR' => 'Cosmétiques',
                'es_ES' => 'Cosméticos',
                'it_IT' => 'Cosmetici',
            ],
            'fields' => [
                'section_general' => [],
                'name' => ['required' => true],
                'description' => [],

                'section_attributes' => [],
                'volume' => [],
                'skin_type' => [
                    'options' => [
                        'Normal',
                        'Dry',
                        'Oily',
                        'Combination',
                        'Sensitive',
                    ],
                ],
                'ingredients' => [],

                'section_marketing' => [],
                'marketplace_enabled' => [],
                'marketplace_title' => [],
                'marketplace_description' => [],
                'marketplace_category' => [
                    'options' => [
                        'Skincare',
                        'Body Care',
                        'Hair Care',
                        'Cleansing',
                    ],
                ],

                'section_media' => [],
                'product_image' => [],
                'product_datasheet' => [],
            ],
        ],

        'food_beverage' => [
            'title' => [
                'en_GB' => 'Food & Beverage',
                'de_DE' => 'Lebensmittel & Getränke',
                'fr_FR' => 'Alimentation & boissons',
                'es_ES' => 'Alimentos y bebidas',
                'it_IT' => 'Alimenti e bevande',
            ],
            'fields' => [
                'section_general' => [],
                'name' => ['required' => true],
                'description' => [],

                'section_attributes' => [],
                'net_weight' => [],
                'ingredients' => [],
                'allergens' => [
                    'options' => [
                        'Gluten',
                        'Milk',
                        'Eggs',
                        'Soy',
                        'Peanuts',
                        'Tree nuts',
                        'Sesame',
                    ],
                ],
                'storage_instructions' => [],

                'section_marketing' => [],
                'marketplace_enabled' => [],
                'marketplace_title' => [],
                'marketplace_description' => [],
                'marketplace_category' => [
                    'options' => [
                        'Food',
                        'Beverages',
                        'Snacks',
                        'Pantry',
                    ],
                ],

                'section_media' => [],
                'product_image' => [],
                'product_datasheet' => [],
            ],
        ],
    ];
}
