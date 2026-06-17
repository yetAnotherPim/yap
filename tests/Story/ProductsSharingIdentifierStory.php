<?php

declare(strict_types=1);

namespace App\Tests\Story;

use App\Entity\Product\Identifier\Type\Enum\DataType;
use App\Tests\Factory\Product\Identifier\IdentifierFactory;
use App\Tests\Factory\Product\Identifier\Type\IdentifierTypeFactory;
use App\Tests\Factory\Product\ProductFactory;
use Zenstruck\Foundry\Story;

final class ProductsSharingIdentifierStory extends Story
{
    public function build(): void
    {
        $identifierType1 = IdentifierTypeFactory::createOne(['title' => 'GTIN', 'dataType' => DataType::String]);
        $identifierType2 = IdentifierTypeFactory::createOne(['title' => 'Internal', 'dataType' => DataType::Integer]);

        ProductFactory::createMany(2,
            [
                'identifiers' => [
                    IdentifierFactory::new(
                        [
                            'value' => '04012345123456',
                            'type' => $identifierType1,
                        ]
                    ),
                    IdentifierFactory::new(
                        [
                            'value' => '12346',
                            'type' => $identifierType2,
                        ]
                    ),
                ],
            ]
        );
    }
}
