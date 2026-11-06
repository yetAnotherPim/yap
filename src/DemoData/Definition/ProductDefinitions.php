<?php

declare(strict_types=1);

namespace App\DemoData\Definition;

class ProductDefinitions
{
    public const string PRODUCT_IMAGE = '2026/11/05/sample-image-4ccbf00bcb77fe084e6a8da261c522d606c785ec.png';

    public const string PRODUCT_DATASHEET = '2026/11/05/yap-sample-product-data-sheet-small-62cbd7965833fc64ec6cf3f387a04fe16e12f727.pdf';

    public const array PRODUCTS = [
        'apparel' => [
            'defaults' => [
                'values' => [
                    'marketplace_enabled' => '1',
                    'marketplace_category' => 'Clothing',
                    'product_image' => self::PRODUCT_IMAGE,
                    'product_datasheet' => self::PRODUCT_DATASHEET,
                ],
                'translations' => [],
            ],
            'products' => [
                [
                    'values' => [
                        'name' => 'Essential Cotton T-Shirt',
                        'description' => 'A versatile everyday T-shirt made from soft organic cotton.',
                        'material' => '100% organic cotton',
                        'care_instructions' => 'Machine wash 30°C,Do not bleach,Do not tumble dry',
                        'marketplace_title' => 'Essential Organic Cotton T-Shirt',
                        'marketplace_description' => 'A comfortable everyday essential made from soft organic cotton.',
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
                        'marketplace_title' => [
                            'de_DE' => 'Klassisches T-Shirt aus Bio-Baumwolle',
                            'fr_FR' => 'T-shirt classique en coton biologique',
                            'es_ES' => 'Camiseta clásica de algodón orgánico',
                        ],
                    ],
                    'variations' => ['colour', 'size', 'fit'],
                    'name_suffix_fields' => ['colour'],
                ],
                [
                    'values' => [
                        'name' => 'Classic Cotton Hoodie',
                        'description' => 'A comfortable cotton hoodie for everyday wear.',
                        'material' => 'Cotton and recycled polyester',
                        'care_instructions' => 'Machine wash 30°C,Do not bleach,Iron at low temperature',
                        'marketplace_title' => 'Classic Everyday Cotton Hoodie',
                        'marketplace_description' => 'A versatile hoodie with a soft brushed interior.',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'Klassischer Baumwoll-Hoodie',
                            'fr_FR' => 'Sweat à capuche classique en coton',
                            'es_ES' => 'Sudadera clásica de algodón con capucha',
                        ],
                        'description' => [
                            'de_DE' => 'Ein bequemer Hoodie aus Baumwolle für den Alltag.',
                            'fr_FR' => 'Un sweat à capuche confortable en coton pour tous les jours.',
                            'es_ES' => 'Una sudadera cómoda de algodón para el día a día.',
                        ],
                        'material' => [
                            'de_DE' => 'Baumwolle und recyceltes Polyester',
                            'fr_FR' => 'Coton et polyester recyclé',
                            'es_ES' => 'Algodón y poliéster reciclado',
                        ],
                    ],
                    'variations' => ['colour', 'size', 'fit'],
                    'name_suffix_fields' => ['colour'],
                ],
                [
                    'values' => [
                        'name' => 'Premium Piqué Polo Shirt',
                        'description' => 'A smart casual polo shirt made from breathable cotton piqué.',
                        'material' => '100% cotton piqué',
                        'care_instructions' => 'Machine wash 30°C,Do not tumble dry,Iron at low temperature',
                        'marketplace_title' => 'Premium Cotton Piqué Polo Shirt',
                        'marketplace_description' => 'A breathable cotton polo shirt for smart casual outfits.',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'Premium-Piqué-Poloshirt',
                            'fr_FR' => 'Polo premium en piqué',
                            'es_ES' => 'Polo premium de piqué',
                        ],
                        'description' => [
                            'de_DE' => 'Ein elegantes Poloshirt aus atmungsaktivem Baumwollpiqué.',
                            'fr_FR' => 'Un polo élégant en piqué de coton respirant.',
                            'es_ES' => 'Un polo elegante de piqué de algodón transpirable.',
                        ],
                        'material' => [
                            'de_DE' => '100 % Baumwollpiqué',
                            'fr_FR' => '100 % piqué de coton',
                            'es_ES' => '100 % piqué de algodón',
                        ],
                    ],
                    'variations' => ['colour', 'size'],
                    'name_suffix_fields' => ['colour'],
                ],
            ],
            'variation_translations' => [
                'colour' => [
                    'Black' => ['de_DE' => 'Schwarz', 'fr_FR' => 'Noir', 'es_ES' => 'Negro'],
                    'White' => ['de_DE' => 'Weiß', 'fr_FR' => 'Blanc', 'es_ES' => 'Blanco'],
                    'Navy' => ['de_DE' => 'Marineblau', 'fr_FR' => 'Bleu marine', 'es_ES' => 'Azul marino'],
                    'Grey' => ['de_DE' => 'Grau', 'fr_FR' => 'Gris', 'es_ES' => 'Gris'],
                    'Red' => ['de_DE' => 'Rot', 'fr_FR' => 'Rouge', 'es_ES' => 'Rojo'],
                    'Green' => ['de_DE' => 'Grün', 'fr_FR' => 'Vert', 'es_ES' => 'Verde'],
                ],
            ],
        ],

        'electronics' => [
            'defaults' => [
                'values' => [
                    'manufacturer' => 'Northstar Electronics',
                    'marketplace_enabled' => '1',
                    'product_image' => self::PRODUCT_IMAGE,
                    'product_datasheet' => self::PRODUCT_DATASHEET,
                ],
                'translations' => [],
            ],
            'products' => [
                [
                    'values' => [
                        'name' => 'Wireless Desktop Speaker',
                        'description' => 'A compact wireless speaker for home and office use.',
                        'model' => 'NS-A200',
                        'voltage' => '5',
                        'power' => '24',
                        'connectivity' => 'Bluetooth,USB-C',
                        'marketplace_title' => 'Northstar Wireless Desktop Speaker',
                        'marketplace_description' => 'Compact desktop speaker with wireless connectivity and USB-C charging.',
                        'marketplace_category' => 'Audio',
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
                    ],
                ],
                [
                    'values' => [
                        'name' => 'Bluetooth Over-Ear Headphones',
                        'description' => 'Comfortable wireless over-ear headphones with active noise cancellation.',
                        'model' => 'NS-H500',
                        'voltage' => '5',
                        'power' => '12',
                        'connectivity' => 'Bluetooth,USB-C,NFC',
                        'marketplace_title' => 'Northstar Bluetooth Over-Ear Headphones',
                        'marketplace_description' => 'Wireless over-ear headphones with active noise cancellation.',
                        'marketplace_category' => 'Audio',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'Kabellose Over-Ear-Kopfhörer',
                            'fr_FR' => 'Casque audio sans fil circum-aural',
                            'es_ES' => 'Auriculares inalámbricos circumaurales',
                        ],
                        'description' => [
                            'de_DE' => 'Bequeme kabellose Over-Ear-Kopfhörer mit aktiver Geräuschunterdrückung.',
                            'fr_FR' => 'Un casque sans fil confortable avec réduction active du bruit.',
                            'es_ES' => 'Auriculares inalámbricos cómodos con cancelación activa de ruido.',
                        ],
                    ],
                ],
                [
                    'values' => [
                        'name' => 'USB-C Multiport Hub',
                        'description' => 'A compact multiport hub for connecting displays and peripherals.',
                        'model' => 'NS-HUB8',
                        'voltage' => '5',
                        'power' => '15',
                        'connectivity' => 'USB-C,HDMI,Ethernet',
                        'marketplace_title' => 'Northstar USB-C Multiport Hub',
                        'marketplace_description' => 'Compact USB-C hub for displays and everyday peripherals.',
                        'marketplace_category' => 'Accessories',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'USB-C-Multiport-Hub',
                            'fr_FR' => 'Hub multiport USB-C',
                            'es_ES' => 'Hub multipuerto USB-C',
                        ],
                        'description' => [
                            'de_DE' => 'Ein kompakter Hub zum Anschließen von Displays und Peripheriegeräten.',
                            'fr_FR' => 'Un hub compact pour connecter des écrans et des périphériques.',
                            'es_ES' => 'Un hub compacto para conectar pantallas y periféricos.',
                        ],
                    ],
                ],
                [
                    'values' => [
                        'name' => 'Wireless Charging Pad',
                        'description' => 'A slim wireless charging pad for compatible mobile devices.',
                        'model' => 'NS-CH10',
                        'voltage' => '5',
                        'power' => '15',
                        'connectivity' => 'NFC,USB-C',
                        'marketplace_title' => 'Northstar Wireless Charging Pad',
                        'marketplace_description' => 'A slim charging pad for compatible wireless charging devices.',
                        'marketplace_category' => 'Accessories',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'Kabellose Ladestation',
                            'fr_FR' => 'Station de recharge sans fil',
                            'es_ES' => 'Base de carga inalámbrica',
                        ],
                        'description' => [
                            'de_DE' => 'Eine flache kabellose Ladestation für kompatible Mobilgeräte.',
                            'fr_FR' => 'Un chargeur sans fil fin pour appareils mobiles compatibles.',
                            'es_ES' => 'Una base de carga inalámbrica y delgada para dispositivos compatibles.',
                        ],
                    ],
                ],
                [
                    'values' => [
                        'name' => 'Portable Bluetooth Speaker',
                        'description' => 'A portable speaker designed for music at home and outdoors.',
                        'model' => 'NS-P300',
                        'voltage' => '5',
                        'power' => '30',
                        'connectivity' => 'Bluetooth,USB-C',
                        'marketplace_title' => 'Northstar Portable Bluetooth Speaker',
                        'marketplace_description' => 'Portable wireless speaker with USB-C charging.',
                        'marketplace_category' => 'Audio',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'Tragbarer Bluetooth-Lautsprecher',
                            'fr_FR' => 'Enceinte Bluetooth portable',
                            'es_ES' => 'Altavoz Bluetooth portátil',
                        ],
                        'description' => [
                            'de_DE' => 'Ein tragbarer Lautsprecher für Musik zu Hause und unterwegs.',
                            'fr_FR' => 'Une enceinte portable pour écouter de la musique à la maison et en extérieur.',
                            'es_ES' => 'Un altavoz portátil para escuchar música en casa y al aire libre.',
                        ],
                    ],
                ],
            ],
            'variation_translations' => [],
        ],

        'furniture' => [
            'defaults' => [
                'values' => [
                    'assembly_required' => '1',
                    'marketplace_enabled' => '1',
                    'product_image' => self::PRODUCT_IMAGE,
                    'product_datasheet' => self::PRODUCT_DATASHEET,
                ],
                'translations' => [],
            ],
            'products' => [
                [
                    'values' => [
                        'name' => 'Minimal Oak Side Table',
                        'description' => 'A minimalist side table with a solid oak top and sturdy legs.',
                        'material' => 'Solid oak and powder-coated steel',
                        'width' => '45',
                        'height' => '50',
                        'depth' => '45',
                        'weight' => '8.5',
                        'marketplace_title' => 'Minimal Oak Side Table',
                        'marketplace_description' => 'A timeless side table combining natural oak and durable steel.',
                        'marketplace_category' => 'Tables',
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
                    ],
                ],
                [
                    'values' => [
                        'name' => 'Ergonomic Office Chair',
                        'description' => 'An adjustable office chair designed for comfortable daily work.',
                        'material' => 'Mesh fabric, aluminium and recycled plastic',
                        'width' => '68',
                        'height' => '118',
                        'depth' => '65',
                        'weight' => '16.5',
                        'marketplace_title' => 'Ergonomic Adjustable Office Chair',
                        'marketplace_description' => 'An ergonomic office chair with adjustable support and breathable mesh.',
                        'marketplace_category' => 'Chairs',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'Ergonomischer Bürostuhl',
                            'fr_FR' => 'Chaise de bureau ergonomique',
                            'es_ES' => 'Silla ergonómica de oficina',
                        ],
                        'description' => [
                            'de_DE' => 'Ein verstellbarer Bürostuhl für komfortables tägliches Arbeiten.',
                            'fr_FR' => 'Une chaise de bureau réglable pour travailler confortablement au quotidien.',
                            'es_ES' => 'Una silla de oficina ajustable para trabajar cómodamente cada día.',
                        ],
                        'material' => [
                            'de_DE' => 'Mesh-Gewebe, Aluminium und recycelter Kunststoff',
                            'fr_FR' => 'Maille, aluminium et plastique recyclé',
                            'es_ES' => 'Tejido de malla, aluminio y plástico reciclado',
                        ],
                    ],
                ],
                [
                    'values' => [
                        'name' => 'Modern Oak Bookshelf',
                        'description' => 'A spacious bookshelf with clean lines and adjustable shelves.',
                        'material' => 'Oak veneer and engineered wood',
                        'width' => '90',
                        'height' => '180',
                        'depth' => '32',
                        'weight' => '38',
                        'marketplace_title' => 'Modern Oak Bookshelf',
                        'marketplace_description' => 'A modern bookshelf with adjustable shelves and an oak finish.',
                        'marketplace_category' => 'Storage',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'Modernes Bücherregal aus Eiche',
                            'fr_FR' => 'Bibliothèque moderne en chêne',
                            'es_ES' => 'Estantería moderna de roble',
                        ],
                        'description' => [
                            'de_DE' => 'Ein geräumiges Bücherregal mit klaren Linien und verstellbaren Einlegeböden.',
                            'fr_FR' => 'Une bibliothèque spacieuse aux lignes épurées avec des étagères réglables.',
                            'es_ES' => 'Una estantería espaciosa de líneas sencillas con baldas ajustables.',
                        ],
                        'material' => [
                            'de_DE' => 'Eichenfurnier und Holzwerkstoff',
                            'fr_FR' => 'Placage chêne et bois composite',
                            'es_ES' => 'Chapa de roble y madera manufacturada',
                        ],
                    ],
                ],
                [
                    'values' => [
                        'name' => 'Height Adjustable Standing Desk',
                        'description' => 'A height-adjustable desk for flexible sitting and standing work.',
                        'material' => 'Bamboo top and steel frame',
                        'width' => '140',
                        'height' => '120',
                        'depth' => '70',
                        'weight' => '32',
                        'marketplace_title' => 'Height Adjustable Standing Desk',
                        'marketplace_description' => 'A flexible standing desk with a durable bamboo top.',
                        'marketplace_category' => 'Tables',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'Höhenverstellbarer Schreibtisch',
                            'fr_FR' => 'Bureau réglable en hauteur',
                            'es_ES' => 'Escritorio regulable en altura',
                        ],
                        'description' => [
                            'de_DE' => 'Ein höhenverstellbarer Schreibtisch für flexibles Arbeiten im Sitzen und Stehen.',
                            'fr_FR' => 'Un bureau réglable en hauteur pour alterner entre position assise et debout.',
                            'es_ES' => 'Un escritorio regulable en altura para alternar entre trabajar sentado y de pie.',
                        ],
                        'material' => [
                            'de_DE' => 'Bambusplatte und Stahlgestell',
                            'fr_FR' => 'Plateau en bambou et piètement en acier',
                            'es_ES' => 'Tablero de bambú y estructura de acero',
                        ],
                    ],
                ],
                [
                    'values' => [
                        'name' => 'Upholstered Lounge Chair',
                        'description' => 'A comfortable lounge chair with a solid wood frame and soft upholstery.',
                        'material' => 'Solid beech wood and woven fabric',
                        'width' => '78',
                        'height' => '82',
                        'depth' => '85',
                        'weight' => '19',
                        'marketplace_title' => 'Upholstered Lounge Chair',
                        'marketplace_description' => 'A relaxed lounge chair with a solid wood frame.',
                        'marketplace_category' => 'Chairs',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'Gepolsterter Loungesessel',
                            'fr_FR' => 'Fauteuil lounge rembourré',
                            'es_ES' => 'Sillón lounge tapizado',
                        ],
                        'description' => [
                            'de_DE' => 'Ein bequemer Loungesessel mit Massivholzgestell und weicher Polsterung.',
                            'fr_FR' => 'Un fauteuil confortable avec une structure en bois massif et un rembourrage doux.',
                            'es_ES' => 'Un sillón cómodo con estructura de madera maciza y tapizado suave.',
                        ],
                        'material' => [
                            'de_DE' => 'Massives Buchenholz und Webstoff',
                            'fr_FR' => 'Hêtre massif et tissu tissé',
                            'es_ES' => 'Madera maciza de haya y tejido',
                        ],
                    ],
                ],
            ],
            'variation_translations' => [],
        ],

        'cosmetics' => [
            'defaults' => [
                'values' => [
                    'marketplace_enabled' => '1',
                    'marketplace_category' => 'Skincare',
                    'product_image' => self::PRODUCT_IMAGE,
                    'product_datasheet' => self::PRODUCT_DATASHEET,
                ],
                'translations' => [],
            ],
            'products' => [
                [
                    'values' => [
                        'name' => 'Daily Hydration Face Cream',
                        'description' => 'A lightweight moisturising face cream for everyday use.',
                        'volume' => '50',
                        'skin_type' => 'Normal,Dry,Sensitive',
                        'ingredients' => 'Aqua, Glycerin, Squalane, Panthenol, Tocopherol',
                        'marketplace_title' => 'Daily Hydration Face Cream 50 ml',
                        'marketplace_description' => 'Lightweight daily face cream with a moisturising formula.',
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
                    ],
                ],
                [
                    'values' => [
                        'name' => 'Gentle Daily Facial Cleanser',
                        'description' => 'A gentle cleanser that removes impurities without drying the skin.',
                        'volume' => '150',
                        'skin_type' => 'Normal,Dry,Sensitive',
                        'ingredients' => 'Aqua, Glycerin, Coco-Glucoside, Panthenol',
                        'marketplace_title' => 'Gentle Daily Facial Cleanser 150 ml',
                        'marketplace_description' => 'A mild daily cleanser for clean and comfortable skin.',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'Sanfter Gesichtsreiniger für jeden Tag',
                            'fr_FR' => 'Nettoyant doux quotidien pour le visage',
                            'es_ES' => 'Limpiador facial suave de uso diario',
                        ],
                        'description' => [
                            'de_DE' => 'Ein sanfter Reiniger, der Unreinheiten entfernt, ohne die Haut auszutrocknen.',
                            'fr_FR' => 'Un nettoyant doux qui élimine les impuretés sans dessécher la peau.',
                            'es_ES' => 'Un limpiador suave que elimina las impurezas sin resecar la piel.',
                        ],
                    ],
                ],
                [
                    'values' => [
                        'name' => 'Nourishing Body Lotion',
                        'description' => 'A rich body lotion for daily hydration and smooth skin.',
                        'volume' => '250',
                        'skin_type' => 'Normal,Dry',
                        'ingredients' => 'Aqua, Shea Butter, Glycerin, Sunflower Oil',
                        'marketplace_title' => 'Nourishing Body Lotion 250 ml',
                        'marketplace_description' => 'A nourishing body lotion with shea butter and sunflower oil.',
                        'marketplace_category' => 'Body Care',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'Pflegende Körperlotion',
                            'fr_FR' => 'Lait corporel nourrissant',
                            'es_ES' => 'Loción corporal nutritiva',
                        ],
                        'description' => [
                            'de_DE' => 'Eine reichhaltige Körperlotion für tägliche Feuchtigkeit und geschmeidige Haut.',
                            'fr_FR' => 'Un lait corporel riche pour hydrater la peau au quotidien.',
                            'es_ES' => 'Una loción corporal rica para hidratar la piel a diario.',
                        ],
                    ],
                ],
                [
                    'values' => [
                        'name' => 'Vitamin C Brightening Serum',
                        'description' => 'A lightweight serum formulated for a brighter-looking complexion.',
                        'volume' => '30',
                        'skin_type' => 'Normal,Oily,Combination',
                        'ingredients' => 'Aqua, Ascorbic Acid, Glycerin, Hyaluronic Acid',
                        'marketplace_title' => 'Vitamin C Brightening Serum 30 ml',
                        'marketplace_description' => 'A lightweight vitamin C serum for daily skincare routines.',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'Aufhellendes Vitamin-C-Serum',
                            'fr_FR' => 'Sérum éclat à la vitamine C',
                            'es_ES' => 'Sérum iluminador con vitamina C',
                        ],
                        'description' => [
                            'de_DE' => 'Ein leichtes Serum für einen strahlender wirkenden Teint.',
                            'fr_FR' => 'Un sérum léger pour un teint plus lumineux.',
                            'es_ES' => 'Un sérum ligero para una piel de aspecto más luminoso.',
                        ],
                    ],
                ],
                [
                    'values' => [
                        'name' => 'Hydrating Daily Shampoo',
                        'description' => 'A gentle shampoo for everyday cleansing and hydration.',
                        'volume' => '300',
                        'skin_type' => 'Normal,Dry',
                        'ingredients' => 'Aqua, Sodium Laureth Sulfate, Cocamidopropyl Betaine, Glycerin',
                        'marketplace_title' => 'Hydrating Daily Shampoo 300 ml',
                        'marketplace_description' => 'A gentle daily shampoo with a hydrating formula.',
                        'marketplace_category' => 'Hair Care',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'Feuchtigkeitsspendendes Shampoo',
                            'fr_FR' => 'Shampooing hydratant quotidien',
                            'es_ES' => 'Champú hidratante de uso diario',
                        ],
                        'description' => [
                            'de_DE' => 'Ein mildes Shampoo für die tägliche Reinigung und Feuchtigkeitspflege.',
                            'fr_FR' => 'Un shampooing doux pour le nettoyage et l’hydratation quotidiens.',
                            'es_ES' => 'Un champú suave para la limpieza e hidratación diaria.',
                        ],
                    ],
                ],
            ],
            'variation_translations' => [],
        ],

        'food_beverage' => [
            'defaults' => [
                'values' => [
                    'marketplace_enabled' => '1',
                    'marketplace_category' => 'Food',
                    'product_image' => self::PRODUCT_IMAGE,
                    'product_datasheet' => self::PRODUCT_DATASHEET,
                    'storage_instructions' => 'Store in a cool, dry place. Keep the package sealed after opening.',
                ],
                'translations' => [
                    'storage_instructions' => [
                        'de_DE' => 'Kühl und trocken lagern. Nach dem Öffnen die Verpackung verschlossen halten.',
                        'fr_FR' => 'Conserver dans un endroit frais et sec. Refermer l’emballage après ouverture.',
                        'es_ES' => 'Conservar en un lugar fresco y seco. Mantener el envase cerrado después de abrirlo.',
                    ],
                ],
            ],
            'products' => [
                [
                    'values' => [
                        'name' => 'Organic Oat Breakfast Mix',
                        'description' => 'A wholesome breakfast mix made with organic oats and seeds.',
                        'net_weight' => '500',
                        'ingredients' => 'Wholegrain oats, sunflower seeds, pumpkin seeds, dried apple',
                        'allergens' => 'Gluten',
                        'marketplace_title' => 'Organic Oat Breakfast Mix 500 g',
                        'marketplace_description' => 'A convenient breakfast mix with organic oats, seeds and dried apple.',
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
                    ],
                ],
                [
                    'values' => [
                        'name' => 'Crunchy Honey Almond Granola',
                        'description' => 'Crunchy oven-baked granola with oats, almonds and honey.',
                        'net_weight' => '400',
                        'ingredients' => 'Wholegrain oats, almonds, honey, sunflower oil',
                        'allergens' => 'Gluten,Tree nuts',
                        'marketplace_title' => 'Crunchy Honey Almond Granola 400 g',
                        'marketplace_description' => 'Oven-baked granola with almonds and honey.',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'Knuspriges Honig-Mandel-Granola',
                            'fr_FR' => 'Granola croustillant au miel et aux amandes',
                            'es_ES' => 'Granola crujiente de miel y almendras',
                        ],
                        'description' => [
                            'de_DE' => 'Knuspriges Ofen-Granola mit Haferflocken, Mandeln und Honig.',
                            'fr_FR' => 'Un granola croustillant cuit au four avec de l’avoine, des amandes et du miel.',
                            'es_ES' => 'Granola crujiente horneado con avena, almendras y miel.',
                        ],
                        'ingredients' => [
                            'de_DE' => 'Vollkornhaferflocken, Mandeln, Honig, Sonnenblumenöl',
                            'fr_FR' => 'Flocons d’avoine complète, amandes, miel, huile de tournesol',
                            'es_ES' => 'Copos de avena integral, almendras, miel, aceite de girasol',
                        ],
                    ],
                ],
                [
                    'values' => [
                        'name' => 'Dark Chocolate Protein Bar',
                        'description' => 'A convenient protein bar with a rich dark chocolate flavour.',
                        'net_weight' => '55',
                        'ingredients' => 'Milk protein, cocoa, dates, almonds',
                        'allergens' => 'Milk,Tree nuts',
                        'marketplace_title' => 'Dark Chocolate Protein Bar 55 g',
                        'marketplace_description' => 'A protein-rich snack with dark chocolate and almonds.',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'Proteinriegel mit Zartbitterschokolade',
                            'fr_FR' => 'Barre protéinée au chocolat noir',
                            'es_ES' => 'Barrita proteica de chocolate negro',
                        ],
                        'description' => [
                            'de_DE' => 'Ein praktischer Proteinriegel mit kräftigem Zartbitterschokoladengeschmack.',
                            'fr_FR' => 'Une barre protéinée pratique au goût intense de chocolat noir.',
                            'es_ES' => 'Una barrita proteica práctica con un intenso sabor a chocolate negro.',
                        ],
                        'ingredients' => [
                            'de_DE' => 'Milchprotein, Kakao, Datteln, Mandeln',
                            'fr_FR' => 'Protéines de lait, cacao, dattes, amandes',
                            'es_ES' => 'Proteína de leche, cacao, dátiles, almendras',
                        ],
                    ],
                ],
                [
                    'values' => [
                        'name' => 'Roasted Mixed Nuts',
                        'description' => 'A roasted mix of nuts with a light touch of sea salt.',
                        'net_weight' => '250',
                        'ingredients' => 'Almonds, cashews, hazelnuts, sea salt',
                        'allergens' => 'Peanuts,Tree nuts',
                        'marketplace_title' => 'Roasted Mixed Nuts 250 g',
                        'marketplace_description' => 'A savoury roasted nut mix with sea salt.',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'Gerösteter Nussmix',
                            'fr_FR' => 'Mélange de noix grillées',
                            'es_ES' => 'Mezcla de frutos secos tostados',
                        ],
                        'description' => [
                            'de_DE' => 'Eine geröstete Nussmischung mit einer leichten Prise Meersalz.',
                            'fr_FR' => 'Un mélange de noix grillées avec une légère touche de sel marin.',
                            'es_ES' => 'Una mezcla de frutos secos tostados con un ligero toque de sal marina.',
                        ],
                        'ingredients' => [
                            'de_DE' => 'Mandeln, Cashewkerne, Haselnüsse, Meersalz',
                            'fr_FR' => 'Amandes, noix de cajou, noisettes, sel marin',
                            'es_ES' => 'Almendras, anacardos, avellanas, sal marina',
                        ],
                    ],
                ],
                [
                    'values' => [
                        'name' => 'Organic Berry Fruit Tea',
                        'description' => 'A fragrant fruit tea blend with hibiscus, berries and apple.',
                        'net_weight' => '100',
                        'ingredients' => 'Hibiscus, dried apple, elderberries, raspberry pieces',
                        'allergens' => '',
                        'marketplace_title' => 'Organic Berry Fruit Tea 100 g',
                        'marketplace_description' => 'A fragrant fruit tea blend with berries and hibiscus.',
                        'marketplace_category' => 'Beverages',
                    ],
                    'translations' => [
                        'name' => [
                            'de_DE' => 'Bio-Früchtetee mit Beeren',
                            'fr_FR' => 'Infusion bio aux fruits rouges',
                            'es_ES' => 'Té de frutas ecológico con frutos rojos',
                        ],
                        'description' => [
                            'de_DE' => 'Eine aromatische Früchteteemischung mit Hibiskus, Beeren und Apfel.',
                            'fr_FR' => 'Un mélange parfumé d’hibiscus, de baies et de pomme.',
                            'es_ES' => 'Una mezcla aromática de hibisco, bayas y manzana.',
                        ],
                        'ingredients' => [
                            'de_DE' => 'Hibiskus, getrockneter Apfel, Holunderbeeren, Himbeerstücke',
                            'fr_FR' => 'Hibiscus, pomme séchée, baies de sureau, morceaux de framboise',
                            'es_ES' => 'Hibisco, manzana deshidratada, bayas de saúco, trozos de frambuesa',
                        ],
                    ],
                ],
            ],
            'variation_translations' => [],
        ],
    ];
}
