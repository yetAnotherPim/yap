<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Product\Field\Type;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Field\Type\Section\FieldSection;
use App\Tests\helper\Trait\SetterGetterTrait;
use App\Tests\helper\Trait\UpdatedAtTrait;
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
            ['setTranslatable', 'isTranslatable', true],
            ['setTranslatable', 'isTranslatable', null, \TypeError::class],
            ['setFilterable', 'isFilterable', true],
            ['setFilterable', 'isFilterable', null, \TypeError::class],
            ['setInputType', 'getInputType', InputType::String],
            ['setInputType', 'getInputType', null, \TypeError::class],
            ['setSection', 'getSection', \Mockery::mock(FieldSection::class)],
            ['setSection', 'getSection', null, \TypeError::class],
            ['setUpdatedAt', 'getUpdatedAt', new \DateTimeImmutable('now')],
            ['setUpdatedAt', 'getUpdatedAt', new \DateTime('now'), \TypeError::class],
            ['setUpdatedAt', 'getUpdatedAt', null],
            ['setCreatedAt', 'getCreatedAt', new \DateTimeImmutable('now')],
            ['setCreatedAt', 'getCreatedAt', new \DateTime('now'), \TypeError::class],
            ['setCreatedAt', 'getCreatedAt', null, \TypeError::class],
        ];
    }
}
