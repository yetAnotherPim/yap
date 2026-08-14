<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Renderer\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Renderer\Field\SelectFieldRenderer;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SelectFieldRenderer::class)]
class SelectFieldRendererTest extends MockeryTestCase
{
    private Field&MockInterface $field;

    private FieldType&MockInterface $fieldType;

    private ProductTypeFieldConfiguration&MockInterface $configuration;

    private SelectFieldRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->field = \Mockery::mock(Field::class);
        $this->fieldType = \Mockery::mock(FieldType::class);
        $this->configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);

        $this->renderer = new SelectFieldRenderer();
    }

    public function testSupportsReturnsTrueForSelectInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Select);

        $this->assertTrue(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testSupportsReturnsFalseForNonSelectInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Text);

        $this->assertFalse(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testRenderReturnsChoiceField(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->twice()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('color');

        $this->fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('Color');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('red');

        $this->configuration
            ->shouldReceive('getOptions')
            ->twice()
            ->andReturn(['red', 'blue', 'green']);

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(true);

        $result = $this->renderer->render($this->field, $this->configuration);

        $this->assertInstanceOf(ChoiceField::class, $result);
    }

    public function testRenderConfiguresSelectFieldCorrectly(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->twice()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('color');

        $this->fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('Color');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('blue');

        $options = ['red', 'blue', 'green'];
        $this->configuration
            ->shouldReceive('getOptions')
            ->twice()
            ->andReturn($options);

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(false);

        $result = $this->renderer->render($this->field, $this->configuration);

        $this->assertInstanceOf(ChoiceField::class, $result);

        $this->assertSame(
            'field_color',
            $result->getAsDto()->getProperty()
        );

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertSame('Color', $formTypeOptions['label']);
        $this->assertEquals('blue', $formTypeOptions['data']);
        $this->assertEquals(array_combine($options, $options), $formTypeOptions['choices']);
        $this->assertFalse($formTypeOptions['mapped']);
        $this->assertFalse($formTypeOptions['required']);
    }
}
