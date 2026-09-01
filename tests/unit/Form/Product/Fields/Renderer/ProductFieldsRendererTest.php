<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Renderer;

use App\Entity\Abstract\TimestampableEntity;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Entity\Product\Type\ProductType;
use App\Form\Product\Fields\Renderer\ProductFieldsRenderer;
use App\Form\Product\Fields\Renderer\Registry\FieldRendererInterface;
use App\Form\Product\Fields\Renderer\Registry\RendererRegistry;
use App\Form\Product\Fields\Renderer\Translation\ProductFieldsTranslationRenderer;
use App\Form\Product\Fields\Resolver\FieldResolver;
use Doctrine\Common\Collections\ArrayCollection;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(ProductFieldsRenderer::class)]
#[UsesClass(Field::class)]
#[UsesClass(TimestampableEntity::class)]
class ProductFieldsRendererTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    /** @var Product&Mockery\MockInterface */
    private Product $product;

    /** @var ProductType&Mockery\MockInterface */
    private ProductType $productType;

    /** @var RendererRegistry&Mockery\MockInterface */
    private RendererRegistry $rendererRegistry;

    /** @var FieldResolver&Mockery\MockInterface */
    private FieldResolver $fieldResolver;

    /** @var TranslatorInterface&Mockery\MockInterface */
    private TranslatorInterface $translator;

    /** @var ProductFieldsTranslationRenderer&Mockery\MockInterface */
    private ProductFieldsTranslationRenderer $translationRenderer;

    private ProductFieldsRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = \Mockery::mock(Product::class);
        $this->productType = \Mockery::mock(ProductType::class);
        $this->rendererRegistry = \Mockery::mock(RendererRegistry::class);
        $this->fieldResolver = \Mockery::mock(FieldResolver::class);
        $this->translator = \Mockery::mock(TranslatorInterface::class);
        $this->translationRenderer = \Mockery::mock(ProductFieldsTranslationRenderer::class);

        $this->renderer = new ProductFieldsRenderer($this->rendererRegistry, $this->fieldResolver, $this->translator, $this->translationRenderer);
    }

    public function testRenderAddsFieldsTab(): void
    {
        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection());

        $this->translationRenderer
            ->shouldReceive('render')
            ->once()
            ->with($this->product)
            ->andReturn([]);

        $result = $this->renderer->render($this->product);

        $this->assertCount(4, $result);
        $this->assertInstanceOf(FormField::class, $result[0]); // Tab
        $this->assertInstanceOf(FormField::class, $result[1]); // Column
        $this->assertInstanceOf(FormField::class, $result[2]); // Fieldset
    }

    public function testRenderSkipsConfigurationWithoutFieldType(): void
    {
        $configuration = \Mockery::mock(
            ProductTypeFieldConfiguration::class
        );

        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([$configuration]));

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturnNull();

        $this->rendererRegistry
            ->shouldNotReceive('getRenderer');

        $this->translationRenderer
            ->shouldReceive('render')
            ->once()
            ->with($this->product)
            ->andReturn([]);

        $result = $this->renderer->render($this->product);

        $this->assertCount(4, $result);
    }

    public function testRenderCreatesFieldAndRendersIt(): void
    {
        $configuration = \Mockery::mock(
            ProductTypeFieldConfiguration::class
        );

        $this->translator
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('de_DE');

        $fieldType = \Mockery::mock(FieldType::class);
        $fieldRenderer = \Mockery::mock(FieldRendererInterface::class);
        $renderedField = TextField::new('field');

        $fieldType
            ->shouldReceive('getTranslatedTitle')
            ->once()
            ->with('de_DE')
            ->andReturn('Translated title');

        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([$configuration]));

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $configuration
            ->shouldReceive('getDefaultValue')
            ->once()
            ->andReturn('default value');

        $field = \Mockery::mock(Field::class);

        $this->fieldResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($this->product, $fieldType, 'default value')
            ->andReturn($field);

        $this->rendererRegistry
            ->shouldReceive('getRenderer')
            ->once()
            ->with($field)
            ->andReturn($fieldRenderer);

        $fieldRenderer
            ->shouldReceive('render')
            ->once()
            ->with($field, $configuration, 'Translated title')
            ->andReturn($renderedField);

        $this->translationRenderer
            ->shouldReceive('render')
            ->once()
            ->with($this->product)
            ->andReturn([]);

        $result = $this->renderer->render($this->product);

        $this->assertCount(5, $result);
        $this->assertSame($renderedField, $result[4]);
    }

    public function testRenderProcessesMultipleConfigurations(): void
    {
        $this->translator
            ->shouldReceive('getLocale')
            ->twice()
            ->andReturn('de_DE');

        $configurationWithoutType = \Mockery::mock(
            ProductTypeFieldConfiguration::class
        );

        $configurationOne = \Mockery::mock(
            ProductTypeFieldConfiguration::class
        );

        $configurationTwo = \Mockery::mock(
            ProductTypeFieldConfiguration::class
        );

        $fieldOne = \Mockery::mock(Field::class);
        $fieldTwo = \Mockery::mock(Field::class);

        $fieldTypeOne = \Mockery::mock(FieldType::class);
        $fieldTypeTwo = \Mockery::mock(FieldType::class);

        $fieldRendererOne = \Mockery::mock(FieldRendererInterface::class);
        $fieldRendererTwo = \Mockery::mock(FieldRendererInterface::class);

        $fieldTypeOne
            ->shouldReceive('getTranslatedTitle')
            ->once()
            ->with('de_DE')
            ->andReturn('Translated title');

        $fieldTypeTwo
            ->shouldReceive('getTranslatedTitle')
            ->once()
            ->with('de_DE')
            ->andReturn('Translated title');

        $renderedFieldOne = TextField::new('fieldOne');
        $renderedFieldTwo = TextField::new('fieldTwo');

        $this->product
            ->shouldReceive('getProductType')
            ->once()
            ->andReturn($this->productType);

        $this->productType
            ->shouldReceive('getProductTypeFieldConfigurations')
            ->once()
            ->andReturn(new ArrayCollection([
                $configurationWithoutType,
                $configurationOne,
                $configurationTwo,
            ]));

        $configurationWithoutType
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturnNull();

        $configurationOne
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldTypeOne);

        $configurationTwo
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldTypeTwo);

        $configurationOne
            ->shouldReceive('getDefaultValue')
            ->once()
            ->andReturn('default value');

        $configurationTwo
            ->shouldReceive('getDefaultValue')
            ->once()
            ->andReturn(null);

        $this->fieldResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($this->product, $fieldTypeOne, 'default value')
            ->andReturn($fieldOne);

        $this->fieldResolver
            ->shouldReceive('resolve')
            ->once()
            ->with($this->product, $fieldTypeTwo, null)
            ->andReturn($fieldTwo);

        $this->rendererRegistry
            ->shouldReceive('getRenderer')
            ->once()
            ->with($fieldOne)
            ->andReturn($fieldRendererOne);

        $this->rendererRegistry
            ->shouldReceive('getRenderer')
            ->once()
            ->with($fieldTwo)
            ->andReturn($fieldRendererTwo);

        $fieldRendererOne
            ->shouldReceive('render')
            ->once()
            ->with($fieldOne, $configurationOne, 'Translated title')
            ->andReturn($renderedFieldOne);

        $fieldRendererTwo
            ->shouldReceive('render')
            ->once()
            ->with($fieldTwo, $configurationTwo, 'Translated title')
            ->andReturn($renderedFieldTwo);

        $this->translationRenderer
            ->shouldReceive('render')
            ->once()
            ->with($this->product)
            ->andReturn([]);

        $result = $this->renderer->render($this->product);

        $this->assertCount(6, $result);
        $this->assertSame($renderedFieldOne, $result[4]);
        $this->assertSame($renderedFieldTwo, $result[5]);
    }
}
