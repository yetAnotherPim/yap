<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Product\Type;

use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Entity\Product\Type\ProductType;
use App\Entity\Product\Type\Translation\ProductTypeTitleTranslation;
use App\Tests\helper\Trait\AssociationTrait;
use App\Tests\helper\Trait\SetterGetterTrait;
use App\Tests\helper\Trait\UpdatedAtTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(ProductType::class)]
class ProductTypeTest extends MockeryTestCase
{
    use SetterGetterTrait;
    use UpdatedAtTrait;
    use AssociationTrait;

    public function testGetId(): void
    {
        $entity = new ProductType();
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
        return ProductType::class;
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
            ['getTranslations', 'addTranslation', 'removeTranslation', ProductTypeTitleTranslation::class, 'setProductType'],
            ['getProductTypeFieldConfigurations', 'addProductTypeFieldConfiguration', 'removeProductTypeFieldConfiguration', ProductTypeFieldConfiguration::class, 'setProductType'],
        ];
    }
}
