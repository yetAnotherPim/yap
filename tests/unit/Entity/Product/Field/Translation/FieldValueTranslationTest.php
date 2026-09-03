<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Product\Field\Translation;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Tests\helper\Trait\Unit\SetterGetterTrait;
use App\Tests\helper\Trait\Unit\UpdatedAtTrait;
use App\Tests\unit\Entity\Product\Field\Type\Enum\InputTypeTest;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(FieldValueTranslation::class)]
#[UsesClass(InputType::class)]
class FieldValueTranslationTest extends MockeryTestCase
{
    use SetterGetterTrait;
    use UpdatedAtTrait;

    public function testGetId(): void
    {
        $entity = new FieldValueTranslation();
        $this->setByReflection($entity, 'id', Uuid::v7());

        $this->assertInstanceOf(UuidV7::class, $entity->getId());
    }

    public function testGetUpdatableRelations(): void
    {
        $entity = new FieldValueTranslation();
        $this->assertEquals([Field::class => 'field'], $entity->getUpdatableRelations());
    }

    #[DataProviderExternal(InputTypeTest::class, 'provideTypedValue')]
    public function testGetTypedValue(
        ?string $value,
        InputType $inputType,
        string|int|bool|float|null $expected,
    ): void {
        $fieldValueTranslation = new FieldValueTranslation();
        $field = \Mockery::mock(Field::class);

        $fieldValueTranslation->setField($field);
        $fieldValueTranslation->setValue($value);

        $fieldType = \Mockery::mock(FieldType::class);

        $field
            ->shouldReceive('getType')
            ->andReturn($fieldType);

        $fieldType
            ->shouldReceive('getInputType')
            ->andReturn($inputType);

        self::assertSame($expected, $fieldValueTranslation->getTypedValue());
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
        return FieldValueTranslation::class;
    }

    /**
     * @return array{array{0:string, 1:string, 2:mixed, 3?:class-string}}
     */
    public static function setterGetterProvider(): array
    {
        return [
            ['setLocale', 'getLocale', \Mockery::mock(Locale::class)],
            ['setLocale', 'getLocale', null, \TypeError::class],
            ['setField', 'getField', \Mockery::mock(Field::class)],
            ['setField', 'getField', null, \TypeError::class],
            ['setValue', 'getValue', 'test'],
            ['setValue', 'getValue', null],
        ];
    }
}
