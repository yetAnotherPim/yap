<?php

declare(strict_types=1);

namespace App\Tests\TestData\Factory\Product\Field\Translation;

use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Tests\TestData\Factory\Locale\LocaleFactory;
use App\Tests\TestData\Factory\Product\Field\FieldFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<FieldValueTranslation>
 */
final class FieldValueTranslationFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return FieldValueTranslation::class;
    }

    #[\Override]
    protected function defaults(): array
    {
        return [
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'field' => FieldFactory::new(),
            'locale' => LocaleFactory::new(['id' => self::faker()->locale()]),
        ];
    }
}
