<?php

declare(strict_types=1);

namespace App\DemoData\Definition;

class ProductDefinitions
{
    public const string PRODUCT_IMAGE = '2026/11/05/sample-image-4ccbf00bcb77fe084e6a8da261c522d606c785ec.png';

    public const string PRODUCT_DATASHEET = '2026/11/05/yap-sample-product-data-sheet-small-62cbd7965833fc64ec6cf3f387a04fe16e12f727.pdf';

    /**
     * @var array<string, array{
     *     values: array<string, string>,
     *     translations: array<string, array<string, string>>
     * }>
     */
    public const array PRODUCTS = [
        'apparel' => [
            'values' => [
                'name' => 'Essential Cotton T-Shirt',
                'description' => 'A versatile everyday T-shirt made from soft organic cotton.',
                'material' => '100% organic cotton',
                'colour' => 'Navy',
                'size' => 'M',
                'fit' => 'Regular fit',
                'care_instructions' => 'Machine wash 30°C,Do not bleach,Do not tumble dry',
                'marketplace_enabled' => '1',
                'marketplace_title' => 'Essential Organic Cotton T-Shirt',
                'marketplace_description' => 'A comfortable everyday essential made from soft organic cotton.',
                'marketplace_category' => 'Clothing',
                'product_image' => self::PRODUCT_IMAGE,
                'product_datasheet' => self::PRODUCT_DATASHEET,
            ],
            'translations' => [
                'name' => [
                    'de_DE' => 'Klassisches Baumwoll-T-Shirt',
                    'fr_FR' => 'T-shirt classique en coton',
                    'es_ES' => 'Camiseta clásica de algodón',
                ],
                'description' => [
                    'de_DE' => 'Ein vielseitiges T-Shirt für jeden Tag aus weicher Bio-Baumwolle.',
                    'fr_FR' => 'Un t-shirt polyvalent pour tous les jours en coton biologique doux.',
                    'es_ES' => 'Una camiseta versátil para el día a día, confeccionada en algodón orgánico suave.',
                ],
                'material' => [
                    'de_DE' => '100 % Bio-Baumwolle',
                    'fr_FR' => '100 % coton biologique',
                    'es_ES' => '100 % algodón orgánico',
                ],
                'care_instructions' => [
                    'de_DE' => 'Maschinenwäsche 30 °C,Nicht bleichen,Nicht im Trockner trocknen',
                    'fr_FR' => 'Lavage en machine à 30 °C,Ne pas blanchir,Ne pas sécher en machine',
                    'es_ES' => 'Lavado a máquina a 30 °C,No usar lejía,No secar en secadora',
                ],
                'marketplace_title' => [
                    'de_DE' => 'Klassisches T-Shirt aus Bio-Baumwolle',
                    'fr_FR' => 'T-shirt classique en coton biologique',
                    'es_ES' => 'Camiseta clásica de algodón orgánico',
                ],
                'marketplace_description' => [
                    'de_DE' => 'Ein bequemes Basic aus weicher Bio-Baumwolle.',
                    'fr_FR' => 'Un basique confortable en coton biologique doux.',
                    'es_ES' => 'Una prenda básica y cómoda de algodón orgánico suave.',
                ],
            ],
        ],

        'electronics' => [
            'values' => [
                'name' => 'Wireless Desktop Speaker',
                'description' => 'A compact wireless speaker for home and office use.',
                'manufacturer' => 'Northstar Audio',
                'model' => 'NS-A200',
                'voltage' => '5',
                'power' => '24',
                'connectivity' => 'Bluetooth,USB-C',
                'marketplace_enabled' => '1',
                'marketplace_title' => 'Northstar Wireless Desktop Speaker',
                'marketplace_description' => 'Compact desktop speaker with wireless connectivity and USB-C charging.',
                'marketplace_category' => 'Audio',
                'product_image' => self::PRODUCT_IMAGE,
                'product_datasheet' => self::PRODUCT_DATASHEET,
            ],
            'translations' => [
                'name' => [
                    'de_DE' => 'Kabelloser Desktop-Lautsprecher',
                    'fr_FR' => 'Enceinte de bureau sans fil',
                    'es_ES' => 'Altavoz inalámbrico de escritorio',
                ],
                'description' => [
                    'de_DE' => 'Ein kompakter kabelloser Lautsprecher für Zuhause und Büro.',
                    'fr_FR' => 'Une enceinte sans fil compacte pour la maison et le bureau.',
                    'es_ES' => 'Un altavoz inalámbrico compacto para el hogar y la oficina.',
                ],
                'marketplace_title' => [
                    'de_DE' => 'Kabelloser Desktop-Lautsprecher von Northstar',
                    'fr_FR' => 'Enceinte de bureau sans fil Northstar',
                    'es_ES' => 'Altavoz inalámbrico de escritorio Northstar',
                ],
                'marketplace_description' => [
                    'de_DE' => 'Kompakter Desktop-Lautsprecher mit kabelloser Verbindung und USB-C-Ladeanschluss.',
                    'fr_FR' => 'Enceinte compacte avec connexion sans fil et recharge USB-C.',
                    'es_ES' => 'Altavoz compacto con conectividad inalámbrica y carga USB-C.',
                ],
            ],
        ],

        'furniture' => [
            'values' => [
                'name' => 'Minimal Oak Side Table',
                'description' => 'A minimalist side table with a solid oak top and sturdy legs.',
                'material' => 'Solid oak and powder-coated steel',
                'width' => '45',
                'height' => '50',
                'depth' => '45',
                'weight' => '8.5',
                'assembly_required' => '1',
                'marketplace_enabled' => '1',
                'marketplace_title' => 'Minimal Oak Side Table',
                'marketplace_description' => 'A timeless side table combining natural oak and durable steel.',
                'marketplace_category' => 'Tables',
                'product_image' => self::PRODUCT_IMAGE,
                'product_datasheet' => self::PRODUCT_DATASHEET,
            ],
            'translations' => [
                'name' => [
                    'de_DE' => 'Minimalistischer Beistelltisch aus Eiche',
                    'fr_FR' => 'Table d’appoint minimaliste en chêne',
                    'es_ES' => 'Mesa auxiliar minimalista de roble',
                ],
                'description' => [
                    'de_DE' => 'Ein minimalistischer Beistelltisch mit massiver Eichenplatte und stabilen Beinen.',
                    'fr_FR' => 'Une table d’appoint minimaliste avec un plateau en chêne massif et des pieds robustes.',
                    'es_ES' => 'Una mesa auxiliar minimalista con tablero de roble macizo y patas resistentes.',
                ],
                'material' => [
                    'de_DE' => 'Massive Eiche und pulverbeschichteter Stahl',
                    'fr_FR' => 'Chêne massif et acier thermolaqué',
                    'es_ES' => 'Roble macizo y acero con recubrimiento en polvo',
                ],
                'marketplace_title' => [
                    'de_DE' => 'Minimalistischer Beistelltisch aus Eiche',
                    'fr_FR' => 'Table d’appoint minimaliste en chêne',
                    'es_ES' => 'Mesa auxiliar minimalista de roble',
                ],
                'marketplace_description' => [
                    'de_DE' => 'Ein zeitloser Beistelltisch aus natürlicher Eiche und robustem Stahl.',
                    'fr_FR' => 'Une table d’appoint intemporelle associant chêne naturel et acier résistant.',
                    'es_ES' => 'Una mesa auxiliar atemporal que combina roble natural y acero resistente.',
                ],
            ],
        ],

        'cosmetics' => [
            'values' => [
                'name' => 'Daily Hydration Face Cream',
                'description' => 'A lightweight moisturising face cream for everyday use.',
                'volume' => '50',
                'skin_type' => 'Normal,Dry,Sensitive',
                'ingredients' => 'Aqua, Glycerin, Squalane, Panthenol, Tocopherol',
                'marketplace_enabled' => '1',
                'marketplace_title' => 'Daily Hydration Face Cream 50 ml',
                'marketplace_description' => 'Lightweight daily face cream with a moisturising formula.',
                'marketplace_category' => 'Skincare',
                'product_image' => self::PRODUCT_IMAGE,
                'product_datasheet' => self::PRODUCT_DATASHEET,
            ],
            'translations' => [
                'name' => [
                    'de_DE' => 'Feuchtigkeitspflege für das Gesicht',
                    'fr_FR' => 'Crème hydratante quotidienne pour le visage',
                    'es_ES' => 'Crema hidratante facial de uso diario',
                ],
                'description' => [
                    'de_DE' => 'Eine leichte Feuchtigkeitscreme für die tägliche Gesichtspflege.',
                    'fr_FR' => 'Une crème hydratante légère pour les soins quotidiens du visage.',
                    'es_ES' => 'Una crema hidratante ligera para el cuidado facial diario.',
                ],
                'ingredients' => [
                    'de_DE' => 'Wasser, Glycerin, Squalan, Panthenol, Tocopherol',
                    'fr_FR' => 'Eau, glycérine, squalane, panthénol, tocophérol',
                    'es_ES' => 'Agua, glicerina, escualano, pantenol, tocoferol',
                ],
                'marketplace_title' => [
                    'de_DE' => 'Tägliche Feuchtigkeitscreme 50 ml',
                    'fr_FR' => 'Crème hydratante quotidienne 50 ml',
                    'es_ES' => 'Crema hidratante diaria 50 ml',
                ],
                'marketplace_description' => [
                    'de_DE' => 'Leichte Tagescreme mit feuchtigkeitsspendender Formulierung.',
                    'fr_FR' => 'Crème légère pour le visage avec une formule hydratante.',
                    'es_ES' => 'Crema facial ligera con una fórmula hidratante.',
                ],
            ],
        ],

        'food_beverage' => [
            'values' => [
                'name' => 'Organic Oat Breakfast Mix',
                'description' => 'A wholesome breakfast mix made with organic oats and seeds.',
                'net_weight' => '500',
                'ingredients' => 'Wholegrain oats, sunflower seeds, pumpkin seeds, dried apple',
                'allergens' => 'Gluten',
                'storage_instructions' => 'Store in a cool, dry place. Keep the package sealed after opening.',
                'marketplace_enabled' => '1',
                'marketplace_title' => 'Organic Oat Breakfast Mix 500 g',
                'marketplace_description' => 'A convenient breakfast mix with organic oats, seeds and dried apple.',
                'marketplace_category' => 'Food',
                'product_image' => self::PRODUCT_IMAGE,
                'product_datasheet' => self::PRODUCT_DATASHEET,
            ],
            'translations' => [
                'name' => [
                    'de_DE' => 'Bio-Hafer-Frühstücksmischung',
                    'fr_FR' => 'Mélange bio pour petit-déjeuner à l’avoine',
                    'es_ES' => 'Mezcla ecológica de avena para el desayuno',
                ],
                'description' => [
                    'de_DE' => 'Eine ausgewogene Frühstücksmischung aus Bio-Hafer und Saaten.',
                    'fr_FR' => 'Un mélange équilibré pour le petit-déjeuner à base d’avoine bio et de graines.',
                    'es_ES' => 'Una mezcla equilibrada para el desayuno elaborada con avena ecológica y semillas.',
                ],
                'ingredients' => [
                    'de_DE' => 'Vollkornhaferflocken, Sonnenblumenkerne, Kürbiskerne, getrockneter Apfel',
                    'fr_FR' => 'Flocons d’avoine complète, graines de tournesol, graines de courge, pomme séchée',
                    'es_ES' => 'Copos de avena integral, semillas de girasol, semillas de calabaza, manzana deshidratada',
                ],
                'storage_instructions' => [
                    'de_DE' => 'Kühl und trocken lagern. Nach dem Öffnen die Verpackung verschlossen halten.',
                    'fr_FR' => 'Conserver dans un endroit frais et sec. Refermer l’emballage après ouverture.',
                    'es_ES' => 'Conservar en un lugar fresco y seco. Mantener el envase cerrado después de abrirlo.',
                ],
                'marketplace_title' => [
                    'de_DE' => 'Bio-Hafer-Frühstücksmischung 500 g',
                    'fr_FR' => 'Mélange bio pour petit-déjeuner à l’avoine 500 g',
                    'es_ES' => 'Mezcla ecológica de avena para el desayuno 500 g',
                ],
                'marketplace_description' => [
                    'de_DE' => 'Praktische Frühstücksmischung mit Bio-Hafer, Saaten und getrocknetem Apfel.',
                    'fr_FR' => 'Mélange pratique avec de l’avoine bio, des graines et de la pomme séchée.',
                    'es_ES' => 'Mezcla práctica con avena ecológica, semillas y manzana deshidratada.',
                ],
            ],
        ],
    ];
}
