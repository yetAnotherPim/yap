<?php

declare(strict_types=1);

namespace App\Tests\TestData\Factory\Product\Identifier\Type;

use App\Entity\Product\Identifier\Type\Enum\DataType;
use App\Entity\Product\Identifier\Type\IdentifierType;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<IdentifierType>
 */
final class IdentifierTypeFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return IdentifierType::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'dataType' => self::faker()->randomElement(DataType::cases()),
            'title' => self::faker()->text(255),
            'createdAt' => self::faker()->dateTime(),
        ];
    }
}
