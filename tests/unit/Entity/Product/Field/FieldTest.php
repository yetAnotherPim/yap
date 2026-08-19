<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Product\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Tests\helper\Trait\Unit\AssociationTrait;
use App\Tests\helper\Trait\Unit\SetterGetterTrait;
use App\Tests\helper\Trait\Unit\UpdatedAtTrait;
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
    use AssociationTrait;

    public function testGetId(): void
    {
        $entity = new Field();
        $this->setByReflection($entity, 'id', Uuid::v7());

        $this->assertInstanceOf(UuidV7::class, $entity->getId());
    }

    public function testGetUpdatableRelations(): void
    {
        $entity = new Field();
        $this->assertEquals([Product::class => 'product'], $entity->getUpdatableRelations());
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

    #[DataProvider('provideTypedValue')]
    public function testGetTypedValue(
        ?string $value,
        InputType $inputType,
        string|int|bool|float|null $expected,
    ): void {
        $field = new Field();
        $field->setValue($value);

        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType
            ->shouldReceive('getInputType')
            ->andReturn($inputType);

        $field->setType($fieldType);

        self::assertSame($expected, $field->getTypedValue());
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

    /**
     * @return iterable<string, array{value: string|null, inputType: InputType, expected: string|int|bool|float|null}>
     */
    public static function provideTypedValue(): iterable
    {
        yield 'null' => [
            'value' => null,
            'inputType' => InputType::Text,
            'expected' => null,
        ];

        yield 'checkbox true' => [
            'value' => '1',
            'inputType' => InputType::CheckBox,
            'expected' => true,
        ];

        yield 'checkbox false' => [
            'value' => '0',
            'inputType' => InputType::CheckBox,
            'expected' => false,
        ];

        yield 'number' => [
            'value' => '3.14159265359',
            'inputType' => InputType::Number,
            'expected' => 3.14159265359,
        ];

        yield 'integer' => [
            'value' => '42',
            'inputType' => InputType::Integer,
            'expected' => 42,
        ];

        yield 'string' => [
            'value' => 'Something',
            'inputType' => InputType::Text,
            'expected' => 'Something',
        ];

        yield 'empty string' => [
            'value' => '',
            'inputType' => InputType::Text,
            'expected' => '',
        ];
    }
}
