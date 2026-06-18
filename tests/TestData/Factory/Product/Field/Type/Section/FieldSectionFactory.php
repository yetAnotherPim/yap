<?php

declare(strict_types=1);

namespace App\Tests\TestData\Factory\Product\Field\Type\Section;

use App\Entity\Product\Field\Type\Section\FieldSection;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<FieldSection>
 */
final class FieldSectionFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return FieldSection::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'title' => self::faker()->text(255),
        ];
    }
}
