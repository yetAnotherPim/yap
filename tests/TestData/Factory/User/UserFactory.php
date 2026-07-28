<?php

declare(strict_types=1);

namespace App\Tests\TestData\Factory\User;

use App\Entity\User\User;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return User::class;
    }

    #[\Override]
    protected function defaults(): array
    {
        return [
            'email' => self::faker()->text(180),
            'firstName' => self::faker()->text(255),
            'lastName' => self::faker()->text(255),
            'password' => self::faker()->text(),
            'roles' => [],
            'username' => self::faker()->text(180),
        ];
    }
}
