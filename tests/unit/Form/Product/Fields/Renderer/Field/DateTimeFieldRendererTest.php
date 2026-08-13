<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Renderer\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Renderer\Field\DateTimeFieldRenderer;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DateTimeFieldRenderer::class)]
class DateTimeFieldRendererTest extends MockeryTestCase
{
    private Field&MockInterface $field;

    private FieldType&MockInterface $fieldType;

    private ProductTypeFieldConfiguration&MockInterface $configuration;

    private DateTimeFieldRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->field = \Mockery::mock(Field::class);
        $this->fieldType = \Mockery::mock(FieldType::class);
        $this->configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);

        $this->renderer = new DateTimeFieldRenderer();
    }

    public function testSupportsReturnsTrueForDateTimeInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::DateTime);

        $this->assertTrue(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testSupportsReturnsFalseForNonDateTimeInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Text);

        $this->assertFalse(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testRenderReturnsDateTimeField(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->twice()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('published_at');

        $this->fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('Published At');

        $this->field
            ->shouldReceive('getValue')
            ->twice()
            ->andReturn('2023-10-27 10:00:00');

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(true);

        $result = $this->renderer->render($this->field, $this->configuration);

        $this->assertInstanceOf(DateTimeField::class, $result);
    }

    public function testRenderConfiguresDateTimeFieldCorrectlyWithData(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->twice()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('published_at');

        $this->fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('Published At');

        $this->field
            ->shouldReceive('getValue')
            ->twice()
            ->andReturn('2023-10-27 10:00:00');

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(true);

        $result = $this->renderer->render($this->field, $this->configuration);

        $this->assertInstanceOf(DateTimeField::class, $result);

        $this->assertSame(
            'field_published_at',
            $result->getAsDto()->getProperty()
        );

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertSame('Published At', $formTypeOptions['label']);
        $this->assertInstanceOf(\DateTimeInterface::class, $formTypeOptions['data']);
        $this->assertSame('2023-10-27 10:00:00', $formTypeOptions['data']->format('Y-m-d H:i:s'));
        $this->assertFalse($formTypeOptions['mapped']);
        $this->assertTrue($formTypeOptions['required']);
    }

    public function testRenderConfiguresDateTimeFieldCorrectlyWithoutData(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->twice()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('published_at');

        $this->fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('Published At');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('');

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(false);

        $result = $this->renderer->render($this->field, $this->configuration);

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();
        $this->assertNull($formTypeOptions['data']);
    }
}
