<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Product\Field\Type;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Field\Type\Translation\FieldTypeTitleTranslation;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Tests\helper\Trait\Unit\AssociationTrait;
use App\Tests\helper\Trait\Unit\SetterGetterTrait;
use App\Tests\helper\Trait\Unit\TitleTranslationTrait;
use App\Tests\helper\Trait\Unit\UpdatedAtTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(FieldType::class)]
class FieldTypeTest extends MockeryTestCase
{
    use SetterGetterTrait;
    use UpdatedAtTrait;
    use AssociationTrait;
    /** @use TitleTranslationTrait<FieldTypeTitleTranslation, FieldType> */
    use TitleTranslationTrait;

    public function testGetId(): void
    {
        $entity = new FieldType();
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

    /**
     * @param class-string $targetValue
     */
    #[DataProvider('associationDataProvider')]
    public function testAssociations(string $getter, string $adder, string $remover, string $targetValue, ?string $inverseSetter = null): void
    {
        $this->performOneToManyAssociationTesting($getter, $adder, $remover, $targetValue, $inverseSetter);
    }

    protected function getTargetClass(): string
    {
        return FieldType::class;
    }

    /**
     * @return array{array{0:string, 1:string, 2:mixed, 3?:class-string}}
     */
    public static function setterGetterProvider(): array
    {
        return [
            ['setTitle', 'getTitle', 'field_title'],
            ['setTitle', 'getTitle', null, \TypeError::class],
            ['setCode', 'getCode', 'field_code'],
            ['setCode', 'getCode', null, \TypeError::class],
            ['setTranslatable', 'isTranslatable', true],
            ['setTranslatable', 'isTranslatable', null, \TypeError::class],
            ['setDecimalSeparator', 'getDecimalSeparator', '.'],
            ['setDecimalSeparator', 'getDecimalSeparator', null],
            ['setFilterable', 'isFilterable', true],
            ['setFilterable', 'isFilterable', null, \TypeError::class],
            ['setInputType', 'getInputType', InputType::String],
            ['setInputType', 'getInputType', null, \TypeError::class],
            ['setUpdatedAt', 'getUpdatedAt', new \DateTimeImmutable('now')],
            ['setUpdatedAt', 'getUpdatedAt', new \DateTime('now'), \TypeError::class],
            ['setUpdatedAt', 'getUpdatedAt', null],
            ['setCreatedAt', 'getCreatedAt', new \DateTimeImmutable('now')],
            ['setCreatedAt', 'getCreatedAt', new \DateTime('now'), \TypeError::class],
            ['setCreatedAt', 'getCreatedAt', null, \TypeError::class],
        ];
    }

    /**
     * @return array{array{0:string, 1:string, 2:string, 3:class-string, 4?:string}}
     */
    public static function associationDataProvider(): array
    {
        return [
            ['getTitleTranslations', 'addTitleTranslation', 'removeTitleTranslation', FieldTypeTitleTranslation::class, 'setFieldType'],
            ['getProductTypeFieldConfigurations', 'addProductTypeFieldConfiguration', 'removeProductTypeFieldConfiguration', ProductTypeFieldConfiguration::class, 'setFieldType'],
        ];
    }

    protected function getTargetSetter(): string
    {
        return 'setFieldType';
    }

    protected function getTranslationClass(): string
    {
        return FieldTypeTitleTranslation::class;
    }
}
