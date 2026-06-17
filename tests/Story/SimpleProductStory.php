<?php

declare(strict_types=1);

namespace App\Tests\Story;

use App\Entity\Product\Identifier\Type\Enum\DataType;
use App\Tests\Factory\Product\Identifier\IdentifierFactory;
use App\Tests\Factory\Product\Identifier\Type\IdentifierTypeFactory;
use App\Tests\Factory\Product\ProductFactory;
use Zenstruck\Foundry\Story;

final class SimpleProductStory extends Story
{
    public function build(): void
    {
        $product = ProductFactory::createOne(
            [
                'identifiers' => [
                    IdentifierFactory::new(
                        [
                            'value' => '04012345123456',
                            'type' => IdentifierTypeFactory::new(['title' => 'GTIN', 'dataType' => DataType::String]),
                        ]
                    ),
                    IdentifierFactory::new(
                        [
                            'value' => '12345',
                            'type' => IdentifierTypeFactory::new(['title' => 'Internal', 'dataType' => DataType::Integer]),
                        ]
                    ),
                ],
            ]
        );

        $this->addState('simple_product', $product);
    }
}
