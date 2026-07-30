<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Product\Type\Translation;

use App\Entity\Locale\Locale;
use App\Entity\Product\Type\ProductType;
use App\Entity\Product\Type\Translation\ProductTypeTitleTranslation;
use App\Tests\helper\Trait\Unit\AssociationTrait;
use App\Tests\helper\Trait\Unit\SetterGetterTrait;
use App\Tests\helper\Trait\Unit\UpdatedAtTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(ProductTypeTitleTranslation::class)]
class ProductTypeTitleTranslationTest extends MockeryTestCase
{
    use SetterGetterTrait;
    use UpdatedAtTrait;
    use AssociationTrait;

    public function testGetId(): void
    {
        $entity = new ProductTypeTitleTranslation();
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
        return ProductTypeTitleTranslation::class;
    }

    /**
     * @return array{array{0:string, 1:string, 2:mixed, 3?:class-string}}
     */
    public static function setterGetterProvider(): array
    {
        return [
            ['setLocale', 'getLocale', \Mockery::mock(Locale::class)],
            ['setLocale', 'getLocale', null, \TypeError::class],
            ['setProductType', 'getProductType', \Mockery::mock(ProductType::class)],
            ['setProductType', 'getProductType', null, \TypeError::class],
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
            ['getTranslations', 'addTranslation', 'removeTranslation', ProductTypeTitleTranslation::class, 'setProductType'],
        ];
    }
}
