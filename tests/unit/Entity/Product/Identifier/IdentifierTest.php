<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Product\Identifier;

use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Identifier\Type\Enum\DataType;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Tests\helper\Trait\Unit\SetterGetterTrait;
use App\Tests\helper\Trait\Unit\UpdatedAtTrait;
use App\Tests\helper\Trait\Unit\UpdateRelatedTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(Identifier::class)]
class IdentifierTest extends MockeryTestCase
{
    use SetterGetterTrait;
    use UpdatedAtTrait;
    use UpdateRelatedTrait;

    public function testGetId(): void
    {
        $entity = new Identifier();
        $this->setByReflection($entity, 'id', Uuid::v7());

        $this->assertInstanceOf(UuidV7::class, $entity->getId());
    }

    public function testGetTypedValue(): void
    {
        $identifierTypeMock = \Mockery::mock(IdentifierType::class);
        $entity = new Identifier()->setType($identifierTypeMock);
        $entity->setValue('123456');

        $identifierTypeMock
            ->shouldReceive('getDataType')
            ->once()
            ->andReturn(DataType::Integer);

        $this->assertEquals(123456, $entity->getTypedValue());
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
            ['setUpdatedAt', 'getUpdatedAt', new \DateTimeImmutable('now')],
            ['setUpdatedAt', 'getUpdatedAt', new \DateTime('now'), \TypeError::class],
            ['setUpdatedAt', 'getUpdatedAt', null],
            ['setCreatedAt', 'getCreatedAt', new \DateTimeImmutable('now')],
            ['setCreatedAt', 'getCreatedAt', new \DateTime('now'), \TypeError::class],
            ['setCreatedAt', 'getCreatedAt', null, \TypeError::class],
            ['setValue', 'getTypedValue', 'value'],
            ['setProduct', 'getProduct', \Mockery::mock(Product::class)],
            ['setProduct', 'getProduct', null, \TypeError::class],
        ];
    }
}
