<?php

declare(strict_types=1);

namespace App\Tests\Story;

use App\Entity\Identifier\Type\Enum\DataType;
use App\Tests\Factory\Identifier\IdentifierFactory;
use App\Tests\Factory\Identifier\Type\IdentifierTypeFactory;
use App\Tests\Factory\Product\ProductFactory;
use Zenstruck\Foundry\Story;

final class MultipleProductsStory extends Story
{
    public function build(): void
    {
        $identifierType1 = IdentifierTypeFactory::createOne(['title' => 'GTIN', 'dataType' => DataType::String]);
        $identifierType2 = IdentifierTypeFactory::createOne(['title' => 'Internal', 'dataType' => DataType::Integer]);

        ProductFactory::createOne(
            [
                'identifiers' => [
                    IdentifierFactory::new(
                        [
                            'value' => '01111',
                            'type' => $identifierType1,
                        ]
                    ),
                    IdentifierFactory::new(
                        [
                            'value' => '1111',
                            'type' => $identifierType2,
                        ]
                    ),
                ],
            ]
        );

        ProductFactory::createOne(
            [
                'identifiers' => [
                    IdentifierFactory::new(
                        [
                            'value' => '01111',
                            'type' => $identifierType1,
                        ]
                    ),
                    IdentifierFactory::new(
                        [
                            'value' => '2222',
                            'type' => $identifierType2,
                        ]
                    ),
                ],
            ]
        );

        ProductFactory::createOne(
            [
                'identifiers' => [
                    IdentifierFactory::new(
                        [
                            'value' => '02222',
                            'type' => $identifierType1,
                        ]
                    ),
                    IdentifierFactory::new(
                        [
                            'value' => '3333',
                            'type' => $identifierType2,
                        ]
                    ),
                ],
            ]
        );
    }
}
