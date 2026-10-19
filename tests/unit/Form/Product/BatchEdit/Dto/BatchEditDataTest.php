<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\BatchEdit\Dto;

use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\BatchEdit\Dto\BatchEditData;
use App\Form\Product\BatchEdit\Dto\BatchEditFieldData;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BatchEditData::class)]
class BatchEditDataTest extends MockeryTestCase
{
    public function testFieldsCollectionIsEmptyByDefault(): void
    {
        $data = new BatchEditData();

        $this->assertCount(
            0,
            $data->getFields(),
        );
    }

    public function testAddField(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $field = \Mockery::mock(BatchEditFieldData::class);

        $fieldType
            ->shouldReceive('getCode')
            ->andReturn('sku');

        $field
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $data = new BatchEditData();

        $data->addField($field);

        $this->assertCount(1, $data->getFields());
        $this->assertSame(
            $field,
            $data->getFields()->get('sku'),
        );
    }

    public function testAddFieldIgnoresFieldWithoutFieldType(): void
    {
        $field = \Mockery::mock(BatchEditFieldData::class);

        $field
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturnNull();

        $data = new BatchEditData();

        $data->addField($field);

        $this->assertCount(0, $data->getFields());
    }

    public function testAddFieldIgnoresDuplicateFieldType(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $firstField = \Mockery::mock(BatchEditFieldData::class);
        $secondField = \Mockery::mock(BatchEditFieldData::class);

        $fieldType
            ->shouldReceive('getCode')
            ->andReturn('sku');

        $firstField
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $secondField
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $data = new BatchEditData();

        $data->addField($firstField);
        $data->addField($secondField);

        $this->assertCount(1, $data->getFields());
        $this->assertSame(
            $firstField,
            $data->getFields()->get('sku'),
        );
    }

    public function testHasFieldReturnsTrueForExistingField(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $field = \Mockery::mock(BatchEditFieldData::class);

        $fieldType
            ->shouldReceive('getCode')
            ->andReturn('sku');

        $field
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $data = new BatchEditData();

        $data->addField($field);

        $this->assertTrue(
            $data->hasField($fieldType),
        );
    }

    public function testHasFieldReturnsFalseForUnknownField(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('sku');

        $data = new BatchEditData();

        $this->assertFalse(
            $data->hasField($fieldType),
        );
    }

    public function testRemoveField(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $field = \Mockery::mock(BatchEditFieldData::class);

        $fieldType
            ->shouldReceive('getCode')
            ->andReturn('sku');

        $field
            ->shouldReceive('getFieldType')
            ->twice()
            ->andReturn($fieldType);

        $data = new BatchEditData();

        $data->addField($field);
        $data->removeField($field);

        $this->assertCount(0, $data->getFields());
        $this->assertFalse(
            $data->hasField($fieldType),
        );
    }

    public function testRemoveFieldIgnoresFieldWithoutFieldType(): void
    {
        $field = \Mockery::mock(BatchEditFieldData::class);

        $field
            ->shouldReceive('getFieldType')
            ->andReturnNull();

        $data = new BatchEditData();

        $data->removeField($field);

        $this->assertCount(0, $data->getFields());
    }

    public function testRemoveUnknownFieldDoesNothing(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $field = \Mockery::mock(BatchEditFieldData::class);

        $fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('sku');

        $field
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $data = new BatchEditData();

        $data->removeField($field);

        $this->assertCount(0, $data->getFields());
    }
}
