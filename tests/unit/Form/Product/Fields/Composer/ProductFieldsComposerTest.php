<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Composer;

use App\Entity\Abstract\TimestampableEntity;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Product;
use App\Form\Product\Fields\Composer\ProductFieldsComposer;
use App\Form\Product\Fields\Composer\Translation\ProductFieldTranslationComposer;
use App\Form\Product\Fields\Renderer\ProductFieldsRenderer;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use Mockery;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ProductFieldsComposer::class)]
#[UsesClass(Field::class)]
#[UsesClass(TimestampableEntity::class)]
class ProductFieldsComposerTest extends Mockery\Adapter\Phpunit\MockeryTestCase
{
    /** @var Product&Mockery\MockInterface */
    private Product $product;

    /** @var ProductFieldsRenderer&Mockery\MockInterface */
    private ProductFieldsRenderer $productFieldsRenderer;

    /** @var ProductFieldTranslationComposer&Mockery\MockInterface */
    private ProductFieldTranslationComposer $fieldTranslationComposer;

    private ProductFieldsComposer $composer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = \Mockery::mock(Product::class);
        $this->productFieldsRenderer = \Mockery::mock(ProductFieldsRenderer::class);
        $this->fieldTranslationComposer = \Mockery::mock(ProductFieldTranslationComposer::class);

        $this->composer = new ProductFieldsComposer($this->fieldTranslationComposer, $this->productFieldsRenderer);
    }

    public function testComposeAddsFields(): void
    {
        $this->productFieldsRenderer
            ->shouldReceive('render')
            ->once()
            ->with($this->product)
            ->andReturn([BooleanField::new('field')]);

        $this->fieldTranslationComposer
            ->shouldReceive('compose')
            ->once()
            ->with($this->product)
            ->andReturn([LocaleField::new('locale')]);

        $result = $this->composer->compose($this->product);

        $this->assertCount(6, $result);
        $this->assertInstanceOf(FormField::class, $result[0]); // Tab
        $this->assertInstanceOf(FormField::class, $result[1]); // Column
        $this->assertInstanceOf(FormField::class, $result[2]); // Fieldset
        $this->assertInstanceOf(LocaleField::class, $result[3]); // Locale field spacer
        $this->assertInstanceOf(BooleanField::class, $result[4]);
        $this->assertInstanceOf(LocaleField::class, $result[5]);
    }
}
