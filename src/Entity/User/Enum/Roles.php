<?php

declare(strict_types=1);

namespace App\Entity\User\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Roles: string implements TranslatableInterface
{
    case INACTIVE = 'inactive';
    case ROLE_EDITOR = 'Editor';
    case ROLE_ADMIN = 'Admin';
    case ROLE_SUPER_ADMIN = 'Super-Admin';
    case ROLE_API_USER = 'API-User';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans(
            match ($this) {
                self::INACTIVE => 'inactive',
                self::ROLE_EDITOR => 'Editor',
                self::ROLE_ADMIN => 'Admin',
                self::ROLE_SUPER_ADMIN => 'Super-Admin',
                self::ROLE_API_USER => 'API-User',
            },
            locale: $locale
        );
    }

    public static function fromName(string $name): self
    {
        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        throw new \ValueError(\sprintf('Unknown role "%s".', $name));
    }
}
