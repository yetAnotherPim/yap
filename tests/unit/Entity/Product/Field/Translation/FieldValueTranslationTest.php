<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Product\Field\Translation;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Tests\helper\Trait\Unit\SetterGetterTrait;
use App\Tests\helper\Trait\Unit\UpdatedAtTrait;
use App\Tests\helper\Trait\Unit\UpdateRelatedTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(FieldValueTranslation::class)]
class FieldValueTranslationTest extends MockeryTestCase
{
    use SetterGetterTrait;
    use UpdatedAtTrait;
    use UpdateRelatedTrait;

    public function testGetId(): void
    {
        $entity = new FieldValueTranslation();
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
