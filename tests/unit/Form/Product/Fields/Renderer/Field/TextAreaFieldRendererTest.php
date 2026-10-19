<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Renderer\Field;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\DefaultTranslation\ProductTypeFieldConfigurationDefaultTranslation;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Renderer\Field\TextAreaFieldRenderer;
use App\Form\ProductType\DefaultTranslation\Resolver\DefaultTranslationResolver;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

#[CoversClass(TextAreaFieldRenderer::class)]
#[UsesClass(InputType::class)]
class TextAreaFieldRendererTest extends MockeryTestCase
{
    private Field&MockInterface $field;
    private FieldType&MockInterface $fieldType;
    private ProductTypeFieldConfiguration&MockInterface $configuration;
    private DefaultTranslationResolver&MockInterface $defaultTranslationResolver;
    private TextAreaFieldRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->field = \Mockery::mock(Field::class);
        $this->fieldType = \Mockery::mock(FieldType::class);
        $this->configuration = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $this->defaultTranslationResolver = \Mockery::mock(DefaultTranslationResolver::class);

        $this->renderer = new TextAreaFieldRenderer($this->defaultTranslationResolver);
    }

    public function testSupportsReturnsFalseForBooleanInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::CheckBox);

        $this->assertFalse(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testSupportsReturnsTrueForTextAreaInputType(): void
    {
        $this->fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Text);

        $this->assertTrue(
            $this->renderer->supports($this->fieldType)
        );
    }

    public function testRenderReturnsTextAreaField(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('description');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('test');

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(true);

        $result = $this->renderer->render($this->field, $this->configuration, 'label');

        $this->assertInstanceOf(TextareaField::class, $result);
    }

    public function testRenderConfiguresTextAreaFieldCorrectly(): void
    {
        $this->field
            ->shouldReceive('getType')
            ->once()
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('description');

        $this->field
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('test');

        $this->configuration
            ->shouldReceive('isRequired')
            ->once()
            ->andReturn(false);

        $result = $this->renderer->render($this->field, $this->configuration, 'label');

        $this->assertInstanceOf(TextareaField::class, $result);

        $this->assertSame(
            'field_description',
            $result->getAsDto()->getProperty()
        );

        $formTypeOptions = $result->getAsDto()->getFormTypeOptions();

        $this->assertSame('label', $formTypeOptions['label']);
        $this->assertEquals('test', $formTypeOptions['data']);
        $this->assertFalse($formTypeOptions['mapped']);
        $this->assertFalse($formTypeOptions['required']);
    }

    public function testRenderWithLocale(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale->shouldReceive('getId')->andReturn('de');

        $this->field
            ->shouldReceive('getType')
            ->times(3)
            ->andReturn($this->fieldType);

        $this->fieldType
            ->shouldReceive('getCode')
            ->once()
            ->andReturn('description');

        $this->fieldType
            ->shouldReceive('isTranslatable')
            ->twice()
            ->andReturn(false);

        $result = $this->renderer->render($this->field, $this->configuration, 'label', $locale);

        $this->assertInstanceOf(TextareaField::class, $result);
        $this->assertSame('field_description-de', $result->getAsDto()->getProperty());
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
            ->andReturn('description');

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
            ->andReturn('Default description');

        $result = $this->renderer->render(
            $this->field,
            $this->configuration,
            'label',
            $locale,
        );

        $this->assertSame(
            'Default description',
            $result->getAsDto()->getFormTypeOptions()['data'],
        );
    }

    public function testGetBatchEditFormType(): void
    {
        $this->assertSame(TextareaType::class, $this->renderer->getBatchEditFormType());
    }

    public function testGetBatchEditOptions(): void
    {
        $this->assertSame(
            [
                'attr' => [
                    'rows' => 4,
                    'class' => 'batch-edit-textarea',
                ],
            ],
            $this->renderer->getBatchEditOptions()
        );
    }
}
