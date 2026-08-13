<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Renderer\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
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

    private ProductTypeFieldConfiguration&MockInterface $configuration;

    private TextFieldRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->field = \Mockery::mock(Field::class);
        $this->fieldType = \Mockery::mock(FieldType::class);
        $this->configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);

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
            ->andReturn('title');

        $this->fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('Title');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('test');

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(true);

        $result = $this->renderer->render($this->field, $this->configuration);

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
            ->andReturn('title');

        $this->fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('Title');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('test');

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(false);

        $result = $this->renderer->render($this->field, $this->configuration);

        $this->assertInstanceOf(TextField::class, $result);

        $this->assertSame(
            'field_title',
            $result->getAsDto()->getProperty()
        );

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertSame('Title', $formTypeOptions['label']);
        $this->assertEquals('test', $formTypeOptions['data']);
        $this->assertFalse($formTypeOptions['mapped']);
        $this->assertFalse($formTypeOptions['required']);
    }
}
