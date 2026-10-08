<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\BatchEdit\Dto;

use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldData;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldTranslationData;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BatchEditFieldData::class)]
class BatchEditFieldDataTest extends MockeryTestCase
{
    public function testGetFieldType(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $data = new BatchEditFieldData($fieldType);

        $this->assertSame(
            $fieldType,
            $data->getFieldType(),
        );
    }

    public function testGetFieldTypeReturnsNull(): void
    {
        $data = new BatchEditFieldData(null);

        $this->assertNull(
            $data->getFieldType(),
        );
    }

    public function testGetValue(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $data = new BatchEditFieldData(
            $fieldType,
            'Test value',
        );

        $this->assertSame(
            'Test value',
            $data->getValue(),
        );
    }

    public function testGetValueReturnsNullByDefault(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $data = new BatchEditFieldData($fieldType);

        $this->assertNull(
            $data->getValue(),
        );
    }

    public function testTranslationsAreEmptyByDefault(): void
    {
        $data = new BatchEditFieldData(null);

        $this->assertCount(
            0,
            $data->getTranslations(),
        );
    }

    public function testAddTranslation(): void
    {
        $data = new BatchEditFieldData(null);

        $translation = \Mockery::mock(
            BatchEditFieldTranslationData::class,
        );

        $data->addTranslation($translation);

        $this->assertCount(
            1,
            $data->getTranslations(),
        );

        $this->assertTrue(
            $data->getTranslations()->contains($translation),
        );
    }

    public function testAddTranslationDoesNotAddDuplicate(): void
    {
        $data = new BatchEditFieldData(null);

        $translation = \Mockery::mock(
            BatchEditFieldTranslationData::class,
        );

        $data->addTranslation($translation);
        $data->addTranslation($translation);

        $this->assertCount(
            1,
            $data->getTranslations(),
        );

        $this->assertSame(
            $translation,
            $data->getTranslations()->first(),
        );
    }

    public function testRemoveTranslation(): void
    {
        $data = new BatchEditFieldData(null);

        $translation = \Mockery::mock(
            BatchEditFieldTranslationData::class,
        );

        $data->addTranslation($translation);

        $data->removeTranslation($translation);

        $this->assertCount(
            0,
            $data->getTranslations(),
        );

        $this->assertFalse(
            $data->getTranslations()->contains($translation),
        );
    }

    public function testRemoveUnknownTranslationDoesNothing(): void
    {
        $data = new BatchEditFieldData(null);

        $translation = \Mockery::mock(
            BatchEditFieldTranslationData::class,
        );

        $data->removeTranslation($translation);

        $this->assertCount(
            0,
            $data->getTranslations(),
        );
    }
}
