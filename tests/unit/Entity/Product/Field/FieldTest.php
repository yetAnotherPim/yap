<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Product\Field;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Tests\helper\Trait\Unit\AssociationTrait;
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

#[CoversClass(Field::class)]
#[UsesClass(InputType::class)]
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

    public function testGetTranslatedTitleReturnsTranslationForLocale(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale
            ->shouldReceive('getId')
            ->andReturn('de_DE');

        $translation = \Mockery::mock(FieldValueTranslation::class);
        $translation
            ->shouldReceive('getLocale')
            ->andReturn($locale);

        $field = new Field()
            ->setValue('Default value');

        $translation
            ->shouldReceive('setField')
            ->once()
            ->with($field)
            ->andReturn($translation);

        $field->addTranslation($translation);

        $this->assertNull(
            $field->getTranslationByLocale('en_GB'),
        );

        $this->assertSame(
            $translation,
            $field->getTranslationByLocale('de_DE'),
        );
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

    #[DataProviderExternal(InputTypeTest::class, 'provideTypedValue')]
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
}
