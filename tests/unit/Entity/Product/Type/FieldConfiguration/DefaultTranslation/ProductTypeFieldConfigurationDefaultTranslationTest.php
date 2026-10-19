<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Product\Type\FieldConfiguration\DefaultTranslation;

use App\Entity\Locale\Locale;
use App\Entity\Product\Type\FieldConfiguration\DefaultTranslation\ProductTypeFieldConfigurationDefaultTranslation;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Tests\helper\Trait\Unit\SetterGetterTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(ProductTypeFieldConfigurationDefaultTranslation::class)]
class ProductTypeFieldConfigurationDefaultTranslationTest extends MockeryTestCase
{
    use SetterGetterTrait;

    public function testGetId(): void
    {
        $entity = new ProductTypeFieldConfigurationDefaultTranslation();
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
        return ProductTypeFieldConfigurationDefaultTranslation::class;
    }

    /**
     * @return array{array{0:string, 1:string, 2:mixed, 3?:class-string}}
     */
    public static function setterGetterProvider(): array
    {
        return [
            ['setLocale', 'getLocale', \Mockery::mock(Locale::class)],
            ['setLocale', 'getLocale', null, \TypeError::class],
            ['setProductTypeFieldConfiguration', 'getProductTypeFieldConfiguration', \Mockery::mock(ProductTypeFieldConfiguration::class)],
            ['setProductTypeFieldConfiguration', 'getProductTypeFieldConfiguration', null, \TypeError::class],
            ['setValue', 'getValue', 'value'],
            ['setValue', 'getValue', null],
        ];
    }
}
