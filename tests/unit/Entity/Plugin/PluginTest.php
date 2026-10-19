<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Plugin;

use App\Entity\Plugin\Plugin;
use App\Tests\helper\Trait\Unit\SetterGetterTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(Plugin::class)]
class PluginTest extends MockeryTestCase
{
    use SetterGetterTrait;

    public function testGetId(): void
    {
        $entity = new Plugin();
        $this->setByReflection($entity, 'id', Uuid::v7());

        $this->assertInstanceOf(UuidV7::class, $entity->getId());
        $this->assertFalse($entity->isActive());
        $this->assertNull($entity->getInstalledVersion());
    }

    public function testIsUpdatable(): void
    {
        $entity = new Plugin();

        $this->assertFalse($entity->isUpdatable());

        $entity
         ->setVersion('0.9.0')
         ->setInstalledVersion('0.9.0');

        $this->assertFalse($entity->isUpdatable());

        $entity
         ->setVersion('0.8.0')
         ->setInstalledVersion('0.9.0');

        $this->assertFalse($entity->isUpdatable());

        $entity
         ->setVersion('0.9.0')
         ->setInstalledVersion('0.8.0');

        $this->assertTrue($entity->isUpdatable());
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
        return Plugin::class;
    }

    /**
     * @return array{array{0:string, 1:string, 2:mixed, 3?:class-string}}
     */
    public static function setterGetterProvider(): array
    {
        return [
            ['setName', 'getName', 'name'],
            ['setName', 'getName', null, \TypeError::class],
            ['setNamespace', 'getNamespace', 'namespace'],
            ['setNamespace', 'getNamespace', null, \TypeError::class],
            ['setActive', 'isActive', true],
            ['setActive', 'isActive', null, \TypeError::class],
            ['setInstalledVersion', 'getInstalledVersion', '0.9.0'],
            ['setInstalledVersion', 'getInstalledVersion', null, \TypeError::class],
            ['setVersion', 'getVersion', '0.9.0'],
            ['setVersion', 'getVersion', null, \TypeError::class],
            ['setAssetCompilationRequired', 'isAssetCompilationRequired', true],
            ['setAssetCompilationRequired', 'isAssetCompilationRequired', null, \TypeError::class],
        ];
    }
}
