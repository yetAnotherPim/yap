<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\User\Enum;

use App\Entity\User\Enum\Roles;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(Roles::class)]
class RolesTest extends MockeryTestCase
{
    #[DataProvider('roleProvider')]
    public function testTransReturnsTranslatedValue(
        Roles $role,
        string $translationKey,
    ): void {
        $translator = \Mockery::mock(TranslatorInterface::class);

        $translator
            ->shouldReceive('trans')
            ->once()
            ->with($translationKey, [], null, 'de_DE')
            ->andReturn('translated-'.$translationKey);

        $this->assertSame(
            'translated-'.$translationKey,
            $role->trans($translator, 'de_DE')
        );
    }

    #[DataProvider('roleProvider')]
    public function testFromNameReturnsMatchingRole(
        Roles $role,
        string $name,
    ): void {
        $this->assertSame($role, Roles::fromName($role->name));
    }

    public function testFromNameThrowsValueErrorForUnknownRole(): void
    {
        $this->expectException(\ValueError::class);
        $this->expectExceptionMessageIs('Unknown role "UNKNOWN".');

        Roles::fromName('UNKNOWN');
    }

    /**
     * @return iterable<string, array{Roles, string}>
     */
    public static function roleProvider(): iterable
    {
        yield 'inactive' => [
            Roles::INACTIVE,
            'inactive',
        ];

        yield 'editor' => [
            Roles::ROLE_EDITOR,
            'Editor',
        ];

        yield 'admin' => [
            Roles::ROLE_ADMIN,
            'Admin',
        ];

        yield 'super admin' => [
            Roles::ROLE_SUPER_ADMIN,
            'Super-Admin',
        ];

        yield 'api user' => [
            Roles::ROLE_API_USER,
            'API-User',
        ];
    }
}
