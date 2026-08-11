<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Renderer\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\Fields\Renderer\Field\TextAreaFieldRenderer;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TextAreaFieldRenderer::class)]
class TextAreaFieldRendererTest extends MockeryTestCase
{
    private Field&MockInterface $field;

    private FieldType&MockInterface $fieldType;

    private TextAreaFieldRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->field = \Mockery::mock(Field::class);
        $this->fieldType = \Mockery::mock(FieldType::class);

        $this->renderer = new TextAreaFieldRenderer();
    }

    public function testSupportsReturnsFalseForBooleanInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Boolean);

        $this->assertFalse(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testSupportsReturnsTrueForTextAreaInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Text);

        $this->assertTrue(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testRenderReturnsTextAreaField(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->twice()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('description');

        $this->fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('description');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('title');

        $result = $this->renderer->render($this->field);

        $this->assertInstanceOf(TextareaField::class, $result);
    }

    public function testRenderConfiguresTextAreaFieldCorrectly(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->twice()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('description');

        $this->fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('Description');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('test');

        $result = $this->renderer->render($this->field);

        $this->assertInstanceOf(TextareaField::class, $result);

        $this->assertSame(
            'field_description',
            $result->getAsDto()->getProperty()
        );

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertSame('Description', $formTypeOptions['label']);
        $this->assertEquals('test', $formTypeOptions['data']);
        $this->assertFalse($formTypeOptions['mapped']);
    }
}
