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
use App\Form\Product\Fields\Resolver\FieldResolver;
use Doctrine\Common\Collections\ArrayCollection;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

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

    private ProductFieldsRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = \Mockery::mock(Product::class);
        $this->productType = \Mockery::mock(ProductType::class);
        $this->rendererRegistry = \Mockery::mock(RendererRegistry::class);
        $this->fieldResolver = \Mockery::mock(FieldResolver::class);

        $this->renderer = new ProductFieldsRenderer($this->rendererRegistry, $this->fieldResolver);
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

        $result = $this->renderer->render($this->product);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(FormField::class, $result[0]);
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

        $result = $this->renderer->render($this->product);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(FormField::class, $result[0]);
    }

    public function testRenderCreatesFieldAndRendersIt(): void
    {
        $configuration = \Mockery::mock(
            ProductTypeFieldConfiguration::class
        );

        $fieldType = \Mockery::mock(FieldType::class);
        $fieldRenderer = \Mockery::mock(FieldRendererInterface::class);
        $renderedField = TextField::new('field');

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
            ->twice()
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
            ->with($field, $configuration)
            ->andReturn($renderedField);

        $result = $this->renderer->render($this->product);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(FormField::class, $result[0]);
        $this->assertSame($renderedField, $result[1]);
    }

    public function testRenderProcessesMultipleConfigurations(): void
    {
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
            ->twice()
            ->andReturn($fieldTypeOne);

        $configurationTwo
            ->shouldReceive('getFieldType')
            ->twice()
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
            ->with($fieldOne, $configurationOne)
            ->andReturn($renderedFieldOne);

        $fieldRendererTwo
            ->shouldReceive('render')
            ->once()
            ->with($fieldTwo, $configurationTwo)
            ->andReturn($renderedFieldTwo);

        $result = $this->renderer->render($this->product);

        $this->assertCount(3, $result);
        $this->assertInstanceOf(FormField::class, $result[0]);
        $this->assertSame($renderedFieldOne, $result[1]);
        $this->assertSame($renderedFieldTwo, $result[2]);
    }
}
