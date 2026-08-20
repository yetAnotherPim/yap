<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Renderer\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Renderer\Field\MultiSelectFieldRenderer;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(MultiSelectFieldRenderer::class)]
#[UsesClass(InputType::class)]
class MultiSelectFieldRendererTest extends MockeryTestCase
{
    private Field&MockInterface $field;

    private FieldType&MockInterface $fieldType;

    private ProductTypeFieldConfiguration&MockInterface $configuration;

    private MultiSelectFieldRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->field = \Mockery::mock(Field::class);
        $this->fieldType = \Mockery::mock(FieldType::class);
        $this->configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);

        $this->renderer = new MultiSelectFieldRenderer();
    }

    public function testSupportsReturnsTrueForMultiSelectInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::MultiSelect);

        $this->assertTrue(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testSupportsReturnsFalseForNonMultiSelectInputType(): void
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
            ->andReturn('tags');

        $this->fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('Tags');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('tag1,tag2');

        $this->configuration
            ->shouldReceive('getOptions')
            ->twice()
            ->andReturn(['tag1', 'tag2', 'tag3']);

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(true);

        $result = $this->renderer->render($this->field, $this->configuration);

        $this->assertInstanceOf(ChoiceField::class, $result);
    }

    public function testRenderConfiguresMultiSelectFieldCorrectly(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->twice()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('tags');

        $this->fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('Tags');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('tag1,tag3');

        $options = ['tag1', 'tag2', 'tag3'];
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
            'field_tags',
            $result->getAsDto()->getProperty()
        );

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertTrue($formTypeOptions['multiple']);
        $this->assertSame('Tags', $formTypeOptions['label']);
        $this->assertEquals(['tag1', 'tag3'], $formTypeOptions['data']);
        $this->assertEquals(array_combine($options, $options), $formTypeOptions['choices']);
        $this->assertFalse($formTypeOptions['mapped']);
        $this->assertFalse($formTypeOptions['required']);
    }

    public function testRenderHandlesNullValue(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->twice()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('tags');

        $this->fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('Tags');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn(null);

        $options = ['tag1', 'tag2'];
        $this->configuration
            ->shouldReceive('getOptions')
            ->twice()
            ->andReturn($options);

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(false);

        $result = $this->renderer->render($this->field, $this->configuration);
        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertEquals([], $formTypeOptions['data']);
    }
}
