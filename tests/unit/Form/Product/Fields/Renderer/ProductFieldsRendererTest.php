<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Renderer;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Entity\Product\Type\ProductType;
use App\Form\Product\Fields\Renderer\ProductFieldsRenderer;
use App\Form\Product\Fields\Renderer\Registry\FieldRendererInterface;
use App\Form\Product\Fields\Renderer\Registry\RendererRegistry;
use App\Form\Product\Fields\Resolver\FieldResolver;
use Doctrine\Common\Collections\ArrayCollection;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(ProductFieldsRenderer::class)]
class ProductFieldsRendererTest extends MockeryTestCase
{
    /** @var RendererRegistry&Mockery\MockInterface */
    private RendererRegistry $rendererRegistry;

    /** @var FieldResolver&Mockery\MockInterface */
    private FieldResolver $fieldResolver;

    /** @var TranslatorInterface&Mockery\MockInterface */
    private TranslatorInterface $translator;

    private ProductFieldsRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rendererRegistry = \Mockery::mock(RendererRegistry::class);
        $this->fieldResolver = \Mockery::mock(FieldResolver::class);
        $this->translator = \Mockery::mock(TranslatorInterface::class);

        $this->renderer = new ProductFieldsRenderer(
            $this->rendererRegistry,
            $this->fieldResolver,
            $this->translator
        );
    }

    public function testRenderReturnsEmptyArrayWhenNoFieldConfigurations(): void
    {
        $product = \Mockery::mock(Product::class);
        $productType = \Mockery::mock(ProductType::class);

        $product->shouldReceive('getProductType')->andReturn($productType);
        $productType->shouldReceive('getProductTypeFieldConfigurations')
            ->andReturn(new ArrayCollection([]));

        $result = $this->renderer->render($product);

        $this->assertSame([], $result);
    }

    public function testRenderSkipsFieldConfigurationWithoutFieldType(): void
    {
        $product = \Mockery::mock(Product::class);
        $productType = \Mockery::mock(ProductType::class);
        $config = \Mockery::mock(ProductTypeFieldConfiguration::class);

        $product->shouldReceive('getProductType')->andReturn($productType);
        $productType->shouldReceive('getProductTypeFieldConfigurations')
            ->andReturn(new ArrayCollection([$config]));

        $config->shouldReceive('getFieldType')->andReturn(null);

        $result = $this->renderer->render($product);

        $this->assertSame([], $result);
    }

    public function testRenderProcessesFieldsWithoutLocale(): void
    {
        $product = \Mockery::mock(Product::class);
        $productType = \Mockery::mock(ProductType::class);
        $config = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $field = \Mockery::mock(Field::class);
        $renderedField = TextField::new('test');

        $product->shouldReceive('getProductType')->andReturn($productType);
        $productType->shouldReceive('getProductTypeFieldConfigurations')
            ->andReturn(new ArrayCollection([$config]));

        $config->shouldReceive('getFieldType')->andReturn($fieldType);
        $config->shouldReceive('getDefaultValue')->andReturn('default-val');

        $this->fieldResolver->shouldReceive('resolve')
            ->once()
            ->with($product, $fieldType, 'default-val')
            ->andReturn($field);

        $this->translator->shouldReceive('getLocale')->andReturn('en');
        $fieldType->shouldReceive('getTranslatedTitleByLocale')->with('en')->andReturn('Field Label');

        $fieldRenderer = \Mockery::mock(FieldRendererInterface::class);
        $this->rendererRegistry->shouldReceive('getRenderer')
            ->with($field)
            ->andReturn($fieldRenderer);

        $fieldRenderer->shouldReceive('render')
            ->once()
            ->with($field, $config, 'Field Label', null)
            ->andReturn($renderedField);

        $result = $this->renderer->render($product);

        $this->assertCount(1, $result);
        $this->assertSame($renderedField, $result[0]);
    }

    public function testRenderProcessesFieldsWithLocale(): void
    {
        $product = \Mockery::mock(Product::class);
        $productType = \Mockery::mock(ProductType::class);
        $config = \Mockery::mock(ProductTypeFieldConfiguration::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $field = \Mockery::mock(Field::class);
        $renderedField = TextField::new('test');
        $locale = \Mockery::mock(Locale::class);

        $product->shouldReceive('getProductType')->andReturn($productType);
        $productType->shouldReceive('getProductTypeFieldConfigurations')
            ->andReturn(new ArrayCollection([$config]));

        $config->shouldReceive('getFieldType')->andReturn($fieldType);

        // When locale is provided, resolve is called with null as default value
        $this->fieldResolver->shouldReceive('resolve')
            ->once()
            ->with($product, $fieldType, null)
            ->andReturn($field);

        $this->translator->shouldReceive('getLocale')->andReturn('de');
        $fieldType->shouldReceive('getTranslatedTitleByLocale')->with('de')->andReturn('Feldbezeichnung');

        $fieldRenderer = \Mockery::mock(FieldRendererInterface::class);
        $this->rendererRegistry->shouldReceive('getRenderer')
            ->with($field)
            ->andReturn($fieldRenderer);

        $fieldRenderer->shouldReceive('render')
            ->once()
            ->with($field, $config, 'Feldbezeichnung', $locale)
            ->andReturn($renderedField);

        $result = $this->renderer->render($product, $locale);

        $this->assertCount(1, $result);
        $this->assertSame($renderedField, $result[0]);
    }
}
