<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Identifier\Type;

use App\Entity\Identifier\Type\Enum\DataType;
use App\Entity\Identifier\Type\IdentifierType;
use App\Tests\helper\Trait\SetterGetterTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(IdentifierType::class)]
class IdentifierTypeTest extends TestCase
{
    use SetterGetterTrait;

    public function testGetId(): void
    {
        $entity = new IdentifierType();
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
        return IdentifierType::class;
    }

    /**
     * @return array{array{0:string, 1:string, 2:mixed, 3?:class-string}}
     */
    public static function setterGetterProvider(): array
    {
        return [
            ['setTitle', 'getTitle', 'title'],
            ['setTitle', 'getTitle', null, \TypeError::class],
            ['setDataType', 'getDataType', DataType::String],
            ['setDataType', 'getDataType', DataType::Integer],
            ['setDataType', 'getDataType', null, \TypeError::class],
        ];
    }
}
