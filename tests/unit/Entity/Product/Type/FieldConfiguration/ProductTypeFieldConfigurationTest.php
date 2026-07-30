<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Product\Type\FieldConfiguration;

use App\Entity\Abstract\TimestampableEntity;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Entity\Product\Type\ProductType;
use App\Tests\helper\Trait\Unit\SetterGetterTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(ProductTypeFieldConfiguration::class)]
#[UsesClass(ProductType::class)]
#[UsesClass(TimestampableEntity::class)]
class ProductTypeFieldConfigurationTest extends MockeryTestCase
{
    use SetterGetterTrait;

    public function testGetId(): void
    {
        $entity = new ProductTypeFieldConfiguration();
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
        return ProductTypeFieldConfiguration::class;
    }

    /**
     * @return array{array{0:string, 1:string, 2:mixed, 3?:class-string}}
     */
    public static function setterGetterProvider(): array
    {
        return [
            ['setPosition', 'getPosition', 0],
            ['setPosition', 'getPosition', null, \TypeError::class],
            ['setDefaultValue', 'getDefaultValue', ''],
            ['setDefaultValue', 'getDefaultValue', null],
            ['setFieldType', 'getFieldType', \Mockery::mock(FieldType::class)],
            ['setFieldType', 'getFieldType', null, \TypeError::class],
            ['setProductType', 'getProductType', \Mockery::mock(ProductType::class)],
            ['setProductType', 'getProductType', null, \TypeError::class],
        ];
    }
}
