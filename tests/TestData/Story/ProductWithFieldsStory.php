<?php

declare(strict_types=1);

namespace App\Tests\TestData\Story;

use App\Tests\TestData\Factory\Product\Field\FieldFactory;
use App\Tests\TestData\Factory\Product\ProductFactory;
use Zenstruck\Foundry\Story;

final class ProductWithFieldsStory extends Story
{
    public function build(): void
    {
        ProductFactory::createMany(2,
            [
                'fields' => [
                    FieldFactory::new(),
                    FieldFactory::new(),
                ],
            ]
        );
    }
}
