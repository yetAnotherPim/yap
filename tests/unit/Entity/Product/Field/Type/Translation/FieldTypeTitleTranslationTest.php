<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Product\Field\Type\Translation;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Field\Type\Translation\FieldTypeTitleTranslation;
use App\Tests\helper\Trait\Unit\SetterGetterTrait;
use App\Tests\helper\Trait\Unit\UpdatedAtTrait;
use App\Tests\helper\Trait\Unit\UpdateRelatedTrait;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(FieldTypeTitleTranslation::class)]
class FieldTypeTitleTranslationTest extends MockeryTestCase
{
    use SetterGetterTrait;
    use UpdatedAtTrait;
    use UpdateRelatedTrait;

    public function testGetId(): void
    {
        $entity = new FieldTypeTitleTranslation();
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
        return FieldTypeTitleTranslation::class;
    }

    /**
     * @return array{array{0:string, 1:string, 2:mixed, 3?:class-string}}
     */
    public static function setterGetterProvider(): array
    {
        return [
            ['setLocale', 'getLocale', \Mockery::mock(Locale::class)],
            ['setLocale', 'getLocale', null, \TypeError::class],
            ['setFieldType', 'getFieldType', \Mockery::mock(FieldType::class)],
            ['setFieldType', 'getFieldType', null, \TypeError::class],
            ['setValue', 'getValue', 'test'],
            ['setValue', 'getValue', null],
        ];
    }
}
