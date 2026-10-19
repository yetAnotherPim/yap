<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Columns\Renderer;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Form\Product\Columns\Renderer\ColumnValueRenderer;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ColumnValueRenderer::class)]
class ColumnValueRendererTest extends MockeryTestCase
{
    private ColumnValueRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->renderer = new ColumnValueRenderer();
    }

    public function testRenderReturnsNullWhenFieldDoesNotExist(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $product = \Mockery::mock(Product::class);
        $product
            ->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection());

        $result = $this->renderer->render($product, $fieldType);

        $this->assertNull($result);
    }

    public function testRenderReturnsFieldValue(): void
    {
        $fieldTypeId = Uuid::v7();

        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType
            ->shouldReceive('getId')
            ->andReturn($fieldTypeId);
        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::String);

        $field = \Mockery::mock(Field::class);
        $field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($fieldType);
        $field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('Test value');

        $product = \Mockery::mock(Product::class);
        $product
            ->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection([$field]));

        $result = $this->renderer->render($product, $fieldType);

        $this->assertSame('Test value', $result);
    }

    public function testRenderReturnsNullForNullCheckboxValue(): void
    {
        $fieldTypeId = Uuid::v7();

        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType
            ->shouldReceive('getId')
            ->andReturn($fieldTypeId);
        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::CheckBox);

        $field = \Mockery::mock(Field::class);
        $field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($fieldType);
        $field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn(null);

        $product = \Mockery::mock(Product::class);
        $product
            ->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection([$field]));

        $result = $this->renderer->render($product, $fieldType);

        $this->assertNull($result);
    }

    public function testRenderReturnsCheckmarkForCheckboxValueOne(): void
    {
        $fieldTypeId = Uuid::v7();

        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType
            ->shouldReceive('getId')
            ->andReturn($fieldTypeId);
        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::CheckBox);

        $field = \Mockery::mock(Field::class);
        $field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($fieldType);
        $field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('1');

        $product = \Mockery::mock(Product::class);
        $product
            ->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection([$field]));

        $result = $this->renderer->render($product, $fieldType);

        $this->assertSame('✓', $result);
    }

    public function testRenderReturnsCrossForCheckboxValueOtherThanOne(): void
    {
        $fieldTypeId = Uuid::v7();

        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType
            ->shouldReceive('getId')
            ->andReturn($fieldTypeId);
        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::CheckBox);

        $field = \Mockery::mock(Field::class);
        $field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($fieldType);
        $field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('0');

        $product = \Mockery::mock(Product::class);
        $product
            ->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection([$field]));

        $result = $this->renderer->render($product, $fieldType);

        $this->assertSame('✕', $result);
    }

    public function testRenderUsesFieldWithMatchingFieldType(): void
    {
        $fieldTypeId = Uuid::v7();
        $otherFieldTypeId = Uuid::v7();

        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType
            ->shouldReceive('getId')
            ->andReturn($fieldTypeId);
        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::String);

        $otherFieldType = \Mockery::mock(FieldType::class);
        $otherFieldType
            ->shouldReceive('getId')
            ->andReturn($otherFieldTypeId);

        $otherField = \Mockery::mock(Field::class);
        $otherField
            ->shouldReceive('getType')
            ->once()
            ->andReturn($otherFieldType);

        $matchingField = \Mockery::mock(Field::class);
        $matchingField
            ->shouldReceive('getType')
            ->once()
            ->andReturn($fieldType);
        $matchingField
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('matching value');

        $product = \Mockery::mock(Product::class);
        $product
            ->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection([
                $otherField,
                $matchingField,
            ]));

        $result = $this->renderer->render($product, $fieldType);

        $this->assertSame('matching value', $result);
    }
}
