<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Renderer\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\Fields\Renderer\Field\CheckboxFieldRenderer;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CheckboxFieldRenderer::class)]
class CheckboxFieldRendererTest extends MockeryTestCase
{
    private Field&MockInterface $field;

    private FieldType&MockInterface $fieldType;

    private CheckboxFieldRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->field = \Mockery::mock(Field::class);
        $this->fieldType = \Mockery::mock(FieldType::class);

        $this->renderer = new CheckboxFieldRenderer();
    }

    public function testSupportsReturnsTrueForBooleanInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Boolean);

        $this->assertTrue(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testSupportsReturnsFalseForNonBooleanInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Text);

        $this->assertFalse(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testRenderReturnsBooleanField(): void
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

        $this->assertInstanceOf(BooleanField::class, $result);
    }

    public function testRenderConfiguresBooleanFieldCorrectly(): void
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

        $this->assertInstanceOf(BooleanField::class, $result);

        $this->assertSame(
            'field_is_active',
            $result->getAsDto()->getProperty()
        );

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertSame('Active', $formTypeOptions['label']);
        $this->assertEquals(true, $formTypeOptions['data']);
        $this->assertFalse($formTypeOptions['mapped']);
    }
}
