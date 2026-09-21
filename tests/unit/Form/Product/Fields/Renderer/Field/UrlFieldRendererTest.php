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
use App\Form\Product\Fields\Renderer\Field\UrlFieldRenderer;
use App\Form\ProductType\DefaultTranslation\Resolver\DefaultTranslationResolver;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(UrlFieldRenderer::class)]
#[UsesClass(InputType::class)]
class UrlFieldRendererTest extends MockeryTestCase
{
    private Field&MockInterface $field;
    private FieldType&MockInterface $fieldType;
    private ProductTypeFieldConfiguration&MockInterface $configuration;
    private DefaultTranslationResolver&MockInterface $defaultTranslationResolver;
    private UrlFieldRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->field = \Mockery::mock(Field::class);
        $this->fieldType = \Mockery::mock(FieldType::class);
        $this->configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $this->defaultTranslationResolver = \Mockery::mock(DefaultTranslationResolver::class);

        $this->renderer = new UrlFieldRenderer($this->defaultTranslationResolver);
    }

    public function testSupportsReturnsTrueForUrlInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Url);

        $this->assertTrue(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testSupportsReturnsFalseForNonUrlInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Text);

        $this->assertFalse(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testRenderReturnsUrlField(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('product_url');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('https://example.com');

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(true);

        $result = $this->renderer->render($this->field, $this->configuration, 'label');

        $this->assertInstanceOf(UrlField::class, $result);
    }

    public function testRenderConfiguresUrlFieldCorrectly(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('product_url');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('https://example.com');

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(false);

        $result = $this->renderer->render($this->field, $this->configuration, 'label');

        $this->assertInstanceOf(UrlField::class, $result);

        $this->assertSame(
            'field_product_url',
            $result->getAsDto()->getProperty()
        );

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertSame('label', $formTypeOptions['label']);
        $this->assertEquals('https://example.com', $formTypeOptions['data']);
        $this->assertFalse($formTypeOptions['mapped']);
        $this->assertFalse($formTypeOptions['required']);
    }

    public function testRenderWithLocale(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale->shouldReceive('getId')->andReturn('de');

        $fieldValueTranslation = \Mockery::mock(FieldValueTranslation::class);

        $this->field
            ->shouldReceive('getType')
            ->times(3)
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('product_url');

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
            ->andReturn('https://example.com');

        $result = $this->renderer->render($this->field, $this->configuration, 'label', $locale);

        $this->assertInstanceOf(UrlField::class, $result);
        $this->assertSame('field_product_url-de', $result->getAsDto()->getProperty());
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
            ->times(3)
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('url');

        $this->fieldType
            ->shouldReceive('isTranslatable')
            ->twice()
            ->andReturn(true);

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
            ->andReturn('https://example.com/de');

        $result = $this->renderer->render(
            $this->field,
            $this->configuration,
            'label',
            $locale,
        );

        $this->assertSame(
            'https://example.com/de',
            $result->getAsDto()->getFormTypeOptions()['data'],
        );
    }
}
