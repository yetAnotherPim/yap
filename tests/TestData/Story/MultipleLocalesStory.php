<?php

declare(strict_types=1);

namespace App\Tests\TestData\Story;

use App\Tests\TestData\Factory\Locale\LocaleFactory;
use Zenstruck\Foundry\Story;

final class MultipleLocalesStory extends Story
{
    public function build(): void
    {
        LocaleFactory::createOne(
            ['id' => 'en_GB', 'default' => true]
        );
        LocaleFactory::createOne(
            ['id' => 'de_DE', 'default' => false]
        );
        LocaleFactory::createOne(
            ['id' => 'es_ES', 'default' => false]
        );
    }
}
