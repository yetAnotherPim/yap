<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Renderer\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Renderer\Field\DateFieldRenderer;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(DateFieldRenderer::class)]
#[UsesClass(InputType::class)]
class DateFieldRendererTest extends MockeryTestCase
{
    private Field&MockInterface $field;

    private FieldType&MockInterface $fieldType;

    private ProductTypeFieldConfiguration&MockInterface $configuration;

    private DateFieldRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->field = \Mockery::mock(Field::class);
        $this->fieldType = \Mockery::mock(FieldType::class);
        $this->configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);

        $this->renderer = new DateFieldRenderer();
    }

    public function testSupportsReturnsTrueForDateInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Date);

        $this->assertTrue(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testSupportsReturnsFalseForNonDateInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Text);

        $this->assertFalse(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testRenderReturnsDateField(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->twice()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('release_date');

        $this->fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('Release Date');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('2023-10-27');

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(true);

        $result = $this->renderer->render($this->field, $this->configuration);

        $this->assertInstanceOf(DateField::class, $result);
    }

    public function testRenderConfiguresDateFieldCorrectlyWithData(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->twice()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('release_date');

        $this->fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('Release Date');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('2023-10-27');

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(true);

        $result = $this->renderer->render($this->field, $this->configuration);

        $this->assertInstanceOf(DateField::class, $result);

        $this->assertSame(
            'field_release_date',
            $result->getAsDto()->getProperty()
        );

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertSame('Release Date', $formTypeOptions['label']);
        $this->assertInstanceOf(\DateTimeInterface::class, $formTypeOptions['data']);
        $this->assertSame('2023-10-27', $formTypeOptions['data']->format('Y-m-d'));
        $this->assertFalse($formTypeOptions['mapped']);
        $this->assertTrue($formTypeOptions['required']);
    }

    public function testRenderConfiguresDateFieldCorrectlyWithoutData(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->twice()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('release_date');

        $this->fieldType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('Release Date');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn(null);

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(false);

        $result = $this->renderer->render($this->field, $this->configuration);

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();
        $this->assertNull($formTypeOptions['data']);
        $this->assertFalse($formTypeOptions['required']);
    }
}
