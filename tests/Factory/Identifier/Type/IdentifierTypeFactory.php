<?php

declare(strict_types=1);

namespace App\Tests\Factory\Identifier\Type;

use App\Entity\Identifier\Type\Enum\DataType;
use App\Entity\Identifier\Type\IdentifierType;
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
        ];
    }
}
