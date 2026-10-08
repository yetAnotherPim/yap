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
use App\Form\Product\Fields\Renderer\Field\MultiSelectFieldRenderer;
use App\Form\ProductType\DefaultTranslation\Resolver\DefaultTranslationResolver;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Form\Extension\Core\Type\TextType;

#[CoversClass(MultiSelectFieldRenderer::class)]
#[UsesClass(InputType::class)]
class MultiSelectFieldRendererTest extends MockeryTestCase
{
    private Field&MockInterface $field;
    private FieldType&MockInterface $fieldType;
    private ProductTypeFieldConfiguration&MockInterface $configuration;
    private DefaultTranslationResolver&MockInterface $defaultTranslationResolver;
    private MultiSelectFieldRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->field = \Mockery::mock(Field::class);
        $this->fieldType = \Mockery::mock(FieldType::class);
        $this->configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $this->defaultTranslationResolver = \Mockery::mock(DefaultTranslationResolver::class);

        $this->renderer = new MultiSelectFieldRenderer($this->defaultTranslationResolver);
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
            ->once()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('tags');

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

        $result = $this->renderer->render($this->field, $this->configuration, 'label');

        $this->assertInstanceOf(ChoiceField::class, $result);
    }

    public function testRenderConfiguresMultiSelectFieldCorrectly(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('tags');

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

        $result = $this->renderer->render($this->field, $this->configuration, 'label');

        $this->assertInstanceOf(ChoiceField::class, $result);

        $this->assertSame(
            'field_tags',
            $result->getAsDto()->getProperty()
        );

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertTrue($formTypeOptions['multiple']);
        $this->assertSame('label', $formTypeOptions['label']);
        $this->assertEquals(['tag1', 'tag3'], $formTypeOptions['data']);
        $this->assertEquals(array_combine($options, $options), $formTypeOptions['choices']);
        $this->assertFalse($formTypeOptions['mapped']);
        $this->assertFalse($formTypeOptions['required']);
    }

    public function testRenderHandlesNullValue(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('tags');

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

        $result = $this->renderer->render($this->field, $this->configuration, 'label');
        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertEquals([], $formTypeOptions['data']);
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
            ->andReturn('tags');

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
            ->andReturn('tag1,tag2');

        $options = ['tag1', 'tag2', 'tag3'];
        $this->configuration
            ->shouldReceive('getOptions')
            ->twice()
            ->andReturn($options);

        $result = $this->renderer->render($this->field, $this->configuration, 'label', $locale);

        $this->assertInstanceOf(ChoiceField::class, $result);
        $this->assertSame('field_tags-de', $result->getAsDto()->getProperty());
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
            ->andReturn('tags');

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
            ->andReturn('tag1,tag3');

        $this->configuration
            ->shouldReceive('getOptions')
            ->twice()
            ->andReturn(['tag1', 'tag2', 'tag3']);

        $result = $this->renderer->render(
            $this->field,
            $this->configuration,
            'label',
            $locale,
        );

        $this->assertSame(
            ['tag1', 'tag3'],
            $result->getAsDto()->getFormTypeOptions()['data'],
        );
    }

    public function testGetBatchEditFormType(): void
    {
        $this->assertSame(TextType::class, $this->renderer->getBatchEditFormType());
    }

    public function testGetBatchEditOptions(): void
    {
        $this->assertSame([], $this->renderer->getBatchEditOptions());
    }
}
