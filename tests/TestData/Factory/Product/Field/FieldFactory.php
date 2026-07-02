<?php

declare(strict_types=1);

namespace App\Tests\TestData\Factory\Product\Field;

use App\Entity\Product\Field\Field;
use App\Tests\TestData\Factory\Product\Field\Type\FieldTypeFactory;
use App\Tests\TestData\Factory\Product\ProductFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Field>
 */
final class FieldFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Field::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'product' => ProductFactory::new(),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'type' => FieldTypeFactory::new(),
            'translations' => [],
        ];
    }
}
