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
use App\Form\Product\Fields\Renderer\Field\DateFieldRenderer;
use App\Form\ProductType\DefaultTranslation\Resolver\DefaultTranslationResolver;
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
    private DefaultTranslationResolver&MockInterface $defaultTranslationResolver;
    private DateFieldRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->field = \Mockery::mock(Field::class);
        $this->fieldType = \Mockery::mock(FieldType::class);
        $this->configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $this->defaultTranslationResolver = \Mockery::mock(DefaultTranslationResolver::class);

        $this->renderer = new DateFieldRenderer($this->defaultTranslationResolver);
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
            ->once()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('release_date');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('2023-10-27');

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(true);

        $result = $this->renderer->render($this->field, $this->configuration, 'label');

        $this->assertInstanceOf(DateField::class, $result);
    }

    public function testRenderConfiguresDateFieldCorrectlyWithData(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('release_date');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('2023-10-27');

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(true);

        $result = $this->renderer->render($this->field, $this->configuration, 'label');

        $this->assertInstanceOf(DateField::class, $result);

        $this->assertSame(
            'field_release_date',
            $result->getAsDto()->getProperty()
        );

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertSame('label', $formTypeOptions['label']);
        $this->assertInstanceOf(\DateTimeInterface::class, $formTypeOptions['data']);
        $this->assertSame('2023-10-27', $formTypeOptions['data']->format('Y-m-d'));
        $this->assertFalse($formTypeOptions['mapped']);
        $this->assertTrue($formTypeOptions['required']);
    }

    public function testRenderConfiguresDateFieldCorrectlyWithoutData(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('release_date');

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
        $this->assertFalse($formTypeOptions['required']);
    }

    public function testRenderWithLocale(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale->shouldReceive('getId')->andReturn('de');

        $fieldValueTranslation = \Mockery::mock(FieldValueTranslation::class);

        $this->field
            ->shouldReceive('getType')
            ->times()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('release_date');

        $this->fieldType
            ->shouldReceive('isTranslatable')
            ->twice()
            ->andReturn(true);

        $this->field
            ->shouldReceive('getTranslationByLocale')
            ->once()
            ->andReturn($fieldValueTranslation);

        $fieldValueTranslation
            ->shouldReceive('getValue')
            ->once()
            ->andReturn(null);

        $result = $this->renderer->render($this->field, $this->configuration, 'label', $locale);

        $this->assertInstanceOf(DateField::class, $result);
        $this->assertSame('field_release_date-de', $result->getAsDto()->getProperty());
        /** @var array<string, mixed> $formTypeOptions */
        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();
        /** @var array<string, mixed> $rowAttr */
        $rowAttr = $formTypeOptions['row_attr'];
        $this->assertSame('de', $rowAttr['data-content-translation-locale']);
    }

    public function testRenderWithLocaleUsesDefaultTranslationWhenFieldTranslationDoesNotExist(): void
    {
        $locale = \Mockery::mock(Locale::class);

        $locale
            ->shouldReceive('getId')
            ->andReturn('de');

        $defaultTranslation = \Mockery::mock(
            ProductTypeFieldConfigurationDefaultTranslation::class,
        );

        $this->field
            ->shouldReceive('getType')
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('release_date');

        $this->fieldType
            ->shouldReceive('isTranslatable')
            ->andReturn(true);

        $this->field
            ->shouldReceive('getTranslationByLocale')
            ->once()
            ->with('de')
            ->andReturnNull();

        $this->defaultTranslationResolver
            ->shouldReceive('resolve')
            ->once()
            ->with(
                $this->configuration,
                $locale,
            )
            ->andReturn($defaultTranslation);

        $defaultTranslation
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('2020-10-27');

        $result = $this->renderer->render(
            $this->field,
            $this->configuration,
            'label',
            $locale,
        );

        $this->assertInstanceOf(\DateTime::class, $result->getAsDto()->getFormTypeOptions()['data']);

        $this->assertSame(
            '2020-10-27',
            $result->getAsDto()->getFormTypeOptions()['data']->format('Y-m-d'),
        );
    }
}
