<?php

declare(strict_types=1);

namespace App\Tests\TestData\Factory\Product\Type\Translation;

use App\Entity\Product\Type\Translation\ProductTypeTitleTranslation;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<ProductTypeTitleTranslation>
 */
final class ProductTypeTitleTranslationFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return ProductTypeTitleTranslation::class;
    }

    #[\Override]
    protected function defaults(): array
    {
        return [
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }
}
