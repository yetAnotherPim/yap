<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Product\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Tests\helper\Trait\Unit\AssociationTrait;
use App\Tests\helper\Trait\Unit\SetterGetterTrait;
use App\Tests\helper\Trait\Unit\UpdatedAtTrait;
use App\Tests\helper\Trait\Unit\UpdateRelatedTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(Field::class)]
class FieldTest extends MockeryTestCase
{
    use SetterGetterTrait;
    use UpdatedAtTrait;
    use UpdateRelatedTrait;
    use AssociationTrait;

    public function testGetId(): void
    {
        $entity = new Field();
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
        return Field::class;
    }

    /**
     * @param class-string $targetValue
     */
    #[DataProvider('associationDataProvider')]
    public function testAssociations(string $getter, string $adder, string $remover, string $targetValue, ?string $inverseSetter = null): void
    {
        $this->performOneToManyAssociationTesting($getter, $adder, $remover, $targetValue, $inverseSetter);
    }

    /**
     * @return array{array{0:string, 1:string, 2:mixed, 3?:class-string}}
     */
    public static function setterGetterProvider(): array
    {
        return [
            ['setType', 'getType', \Mockery::mock(FieldType::class)],
            ['setType', 'getType', null, \TypeError::class],
            ['setProduct', 'getProduct', \Mockery::mock(Product::class)],
            ['setProduct', 'getProduct', null, \TypeError::class],
            ['setValue', 'getValue', 'test'],
            ['setValue', 'getValue', null],
        ];
    }

    /**
     * @return array{array{0:string, 1:string, 2:string, 3:class-string, 4?:string}}
     */
    public static function associationDataProvider(): array
    {
        return [
            ['getTranslations', 'addTranslation', 'removeTranslation', FieldValueTranslation::class, 'setField'],
        ];
    }
}
