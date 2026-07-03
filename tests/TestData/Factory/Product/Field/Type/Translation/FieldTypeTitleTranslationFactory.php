<?php

declare(strict_types=1);

namespace App\Tests\TestData\Factory\Product\Field\Type\Translation;

use App\Entity\Product\Field\Type\Translation\FieldTypeTitleTranslation;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<FieldTypeTitleTranslation>
 */
final class FieldTypeTitleTranslationFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return FieldTypeTitleTranslation::class;
    }

    #[\Override]
    protected function defaults(): array
    {
        return [
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }
}
