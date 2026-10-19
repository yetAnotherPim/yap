<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\User\Configuration;

use App\Entity\Product\Field\Type\FieldType;
use App\Entity\User\Configuration\ProductColumnConfiguration;
use App\Entity\User\User;
use App\Tests\helper\Trait\Unit\SetterGetterTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(ProductColumnConfiguration::class)]
class ProductColumnConfigurationTest extends MockeryTestCase
{
    use SetterGetterTrait;

    /**
     * @throws \ReflectionException
     */
    public function testGetId(): void
    {
        $entity = new ProductColumnConfiguration();
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
        return ProductColumnConfiguration::class;
    }

    /**
     * @return array{array{0:string, 1:string, 2:mixed, 3?:class-string}}
     */
    public static function setterGetterProvider(): array
    {
        return [
            ['setUser', 'getUser', \Mockery::mock(User::class)],
            ['setUser', 'getUser', null, \TypeError::class],
            ['setFieldType', 'getFieldType', \Mockery::mock(FieldType::class)],
            ['setFieldType', 'getFieldType', null, \TypeError::class],
            ['setPosition', 'getPosition', 0],
            ['setPosition', 'getPosition', null, \TypeError::class],
        ];
    }
}
