<?php

declare(strict_types=1);

namespace App\Tests\TestData\Factory\Product\Field\Type;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Tests\TestData\Factory\Product\Field\Type\Section\FieldSectionFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<FieldType>
 */
final class FieldTypeFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return FieldType::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'code' => self::faker()->text(15),
            'title' => self::faker()->text(15),
            'filterable' => self::faker()->boolean(),
            'inputType' => self::faker()->randomElement(InputType::cases()),
            'section' => FieldSectionFactory::new(),
            'translatable' => self::faker()->boolean(),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(FieldType $fieldType): void {})
        ;
    }
}
