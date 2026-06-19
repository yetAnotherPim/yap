<?php

declare(strict_types=1);

namespace App\Tests\TestData\Factory\Locale;

use App\Entity\Locale\Locale;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Locale>
 */
final class LocaleFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return Locale::class;
    }

    #[\Override]
    protected function defaults(): array
    {
        return [
        ];
    }
}
