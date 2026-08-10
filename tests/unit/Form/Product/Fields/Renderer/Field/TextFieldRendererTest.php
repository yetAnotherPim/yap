<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Renderer\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\Fields\Renderer\Field\TextFieldRenderer;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TextFieldRenderer::class)]
class TextFieldRendererTest extends MockeryTestCase
{
    private Field&MockInterface $field;

    private FieldType&MockInterface $fieldType;

    private TextFieldRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->field = \Mockery::mock(Field::class);
        $this->fieldType = \Mockery::mock(FieldType::class);

        $this->renderer = new TextFieldRenderer();
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

    public function testSupportsReturnsTrueForTextInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::String);

        $this->assertTrue(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testRenderReturnsTextField(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->twice()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('is_active');

        $this->fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('Active');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn(true);

        $result = $this->renderer->render($this->field);

        $this->assertInstanceOf(TextField::class, $result);
    }

    public function testRenderConfiguresTextFieldCorrectly(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->twice()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('is_active');

        $this->fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('Active');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('test');

        $result = $this->renderer->render($this->field);

        $this->assertInstanceOf(TextField::class, $result);

        $this->assertSame(
            'is_active',
            $result->getAsDto()->getProperty()
        );

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertSame('Active', $formTypeOptions['label']);
        $this->assertEquals('test', $formTypeOptions['data']);
        $this->assertFalse($formTypeOptions['mapped']);
    }
}
