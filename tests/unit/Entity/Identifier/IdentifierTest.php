<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Identifier;

use App\Entity\Identifier\Identifier;
use App\Tests\helper\Trait\SetterGetterTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(Identifier::class)]
class IdentifierTest extends TestCase
{
    use SetterGetterTrait;

    public function testGetId(): void
    {
        $entity = new Identifier();
        $this->setByReflection($entity, 'id', Uuid::v7());

        $this->assertInstanceOf(UuidV7::class, $entity->getId());
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
        return Identifier::class;
    }

    /**
     * @return array{array{0:string, 1:string, 2:mixed, 3?:class-string}}
     */
    public static function setterGetterProvider(): array
    {
        return [
            ['setValue', 'getValue', 'value'],
            ['setValue', 'getValue', null, \TypeError::class],
        ];
    }
}
