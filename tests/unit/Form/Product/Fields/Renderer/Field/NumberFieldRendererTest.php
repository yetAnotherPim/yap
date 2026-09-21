<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Renderer\Field;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\DefaultTranslation\ProductTypeFieldConfigurationDefaultTranslation;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Renderer\Field\NumberFieldRenderer;
use App\Form\ProductType\DefaultTranslation\Resolver\DefaultTranslationResolver;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(NumberFieldRenderer::class)]
#[UsesClass(InputType::class)]
class NumberFieldRendererTest extends MockeryTestCase
{
    private Field&MockInterface $field;
    private FieldType&MockInterface $fieldType;
    private ProductTypeFieldConfiguration&MockInterface $configuration;
    private DefaultTranslationResolver&MockInterface $defaultTranslationResolver;
    private NumberFieldRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->field = \Mockery::mock(Field::class);
        $this->fieldType = \Mockery::mock(FieldType::class);
        $this->configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $this->defaultTranslationResolver = \Mockery::mock(DefaultTranslationResolver::class);

        $this->renderer = new NumberFieldRenderer($this->defaultTranslationResolver);
    }

    public function testSupportsReturnsTrueForNumberInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Number);

        $this->assertTrue(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testSupportsReturnsFalseForNonNumberInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Text);

        $this->assertFalse(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testRenderReturnsNumberField(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->times(3)
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('price');

        $this->fieldType
            ->shouldReceive('getDecimalSeparator')
            ->twice()
            ->andReturn(',');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('12,34');

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(true);

        $result = $this->renderer->render($this->field, $this->configuration, 'label');

        $this->assertInstanceOf(NumberField::class, $result);
    }

    public function testRenderConfiguresNumberFieldCorrectly(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->times(3)
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('price');

        $this->fieldType
            ->shouldReceive('getDecimalSeparator')
            ->twice()
            ->andReturn(',');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('12,34');

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(true);

        $result = $this->renderer->render($this->field, $this->configuration, 'label');

        $this->assertInstanceOf(NumberField::class, $result);

        $this->assertSame(
            'field_price',
            $result->getAsDto()->getProperty()
        );

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertSame('label', $formTypeOptions['label']);
        $this->assertSame(12.34, $formTypeOptions['data']);
        $this->assertFalse($formTypeOptions['mapped']);
        $this->assertTrue($formTypeOptions['required']);
    }

    public function testRenderConfiguresNumberFieldCorrectlyWithDecimalSeparatorIsNull(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->times(3)
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('price');

        $this->fieldType
            ->shouldReceive('getDecimalSeparator')
            ->twice()
            ->andReturn(null);

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('12.34');

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(true);

        $result = $this->renderer->render($this->field, $this->configuration, 'label');

        $this->assertInstanceOf(NumberField::class, $result);

        $this->assertSame(
            'field_price',
            $result->getAsDto()->getProperty()
        );

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertSame('label', $formTypeOptions['label']);
        $this->assertSame(12.34, $formTypeOptions['data']);
        $this->assertFalse($formTypeOptions['mapped']);
        $this->assertTrue($formTypeOptions['required']);
    }

    public function testRenderHandlesNullValue(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->times(3)
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('price');

        $this->fieldType
            ->shouldReceive('getDecimalSeparator')
            ->twice()
            ->andReturn(',');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn(null);

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(false);

        $result = $this->renderer->render($this->field, $this->configuration, 'label');

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();
        $this->assertNull($formTypeOptions['data']);
    }

    public function testRenderWithLocale(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale->shouldReceive('getId')->andReturn('de');

        $fieldValueTranslation = \Mockery::mock(FieldValueTranslation::class);

        $this->field
            ->shouldReceive('getType')
            ->times(5)
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('price');

        $this->fieldType
            ->shouldReceive('isTranslatable')
            ->twice()
            ->andReturn(true);

        $this->fieldType
            ->shouldReceive('getDecimalSeparator')
            ->twice()
            ->andReturn(',');

        $this->field
            ->shouldReceive('getTranslationByLocale')
            ->once()
            ->andReturn($fieldValueTranslation);

        $fieldValueTranslation
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('12,34');

        $result = $this->renderer->render($this->field, $this->configuration, 'label', $locale);

        $this->assertInstanceOf(NumberField::class, $result);
        $this->assertSame('field_price-de', $result->getAsDto()->getProperty());
        /** @var array<string, mixed> $formTypeOptions */
        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();
        /** @var array<string, mixed> $rowAttr */
        $rowAttr = $formTypeOptions['row_attr'];
        $this->assertSame('de', $rowAttr['data-content-translation-locale']);
    }

    public function testRenderWithLocaleUsesDefaultTranslation(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale->shouldReceive('getId')->andReturn('de');

        $defaultTranslation = \Mockery::mock(
            ProductTypeFieldConfigurationDefaultTranslation::class,
        );

        $this->field
            ->shouldReceive('getType')
            ->times(5)
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('price');

        $this->fieldType
            ->shouldReceive('isTranslatable')
            ->twice()
            ->andReturn(true);

        $this->fieldType
            ->shouldReceive('getDecimalSeparator')
            ->twice()
            ->andReturn(',');

        $this->field
            ->shouldReceive('getTranslationByLocale')
            ->once()
            ->with('de')
            ->andReturnNull();

        $this->defaultTranslationResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($this->configuration, $locale)
            ->andReturn($defaultTranslation);

        $defaultTranslation
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('12,34');

        $result = $this->renderer->render(
            $this->field,
            $this->configuration,
            'label',
            $locale,
        );

        $this->assertSame(
            12.34,
            $result->getAsDto()->getFormTypeOptions()['data'],
        );
    }
}
