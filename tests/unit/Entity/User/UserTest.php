<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\User;

use App\Entity\User\User;
use App\Tests\helper\Trait\SetterGetterTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;
use Webmozart\Assert\InvalidArgumentException;

#[CoversClass(User::class)]
class UserTest extends MockeryTestCase
{
    use SetterGetterTrait;

    public function testGetId(): void
    {
        $entity = new User();
        $this->setByReflection($entity, 'id', Uuid::v7());

        $this->assertInstanceOf(UuidV7::class, $entity->getId());
    }

    public function testGetUserIdentifier(): void
    {
        $entity = new User()->setEmail('test@test.de');
        $this->assertEquals('test@test.de', $entity->getUserIdentifier());

        $entity = new User()->setEmail('');
        $this->expectException(InvalidArgumentException::class);
        $entity->getUserIdentifier();
    }

    /**
     * @param ?class-string $expectedException
     */
    #[DataProvider('setterGetterProvider')]
    public function testSetterGetter(string $setter, string $getter, mixed $value, ?string $expectedException = null): void
    {
        $this->performSetterGetterTesting($setter, $getter, $value, $expectedException);
    }

    protected function getTargetClass(): string
    {
        return User::class;
    }

    /**
     * @return array{array{0:string, 1:string, 2:mixed, 3?:class-string}}
     */
    public static function setterGetterProvider(): array
    {
        return [
            ['setUsername', 'getUsername', 'username'],
            ['setUsername', 'getUsername', null, \TypeError::class],
            ['setFirstName', 'getFirstName', 'FirstName'],
            ['setFirstName', 'getFirstName', null, \TypeError::class],
            ['setLastName', 'getLastName', 'LastName'],
            ['setLastName', 'getLastName', null, \TypeError::class],
            ['setEmail', 'getEmail', 'Email'],
            ['setEmail', 'getEmail', null, \TypeError::class],
            ['setPassword', 'getPassword', 'Password'],
            ['setPassword', 'getPassword', null, \TypeError::class],
            ['setRoles', 'getRoles', ['ROLE_1', 'ROLE_2']],
            ['setRoles', 'getRoles', null, \TypeError::class],
        ];
    }
}
