<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Product;

use App\Entity\Product\Product;
use App\Tests\helper\Trait\SetterGetterTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(Product::class)]
class ProductTest extends TestCase
{
    use SetterGetterTrait;

    public function testGetId(): void
    {
        $entity = new Product();
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
        return Product::class;
    }

    /**
     * @return array{array{0:string, 1:string, 2:mixed, 3?:class-string}}
     */
    public static function setterGetterProvider(): array
    {
        return [
            ['setUpdatedAt', 'getUpdatedAt', new \DateTimeImmutable('now')],
            ['setUpdatedAt', 'getUpdatedAt', new \DateTime('now'), \TypeError::class],
            ['setUpdatedAt', 'getUpdatedAt', null],
            ['setCreatedAt', 'getCreatedAt', new \DateTimeImmutable('now')],
            ['setCreatedAt', 'getCreatedAt', new \DateTime('now'), \TypeError::class],
            ['setCreatedAt', 'getCreatedAt', null, \TypeError::class],
        ];
    }
}
