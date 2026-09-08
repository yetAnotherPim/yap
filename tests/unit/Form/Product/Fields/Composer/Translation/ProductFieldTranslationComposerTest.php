<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Fields\Composer\Translation;

use App\Entity\Locale\Locale;
use App\Entity\Product\Product;
use App\Form\Product\Fields\Composer\Translation\ProductFieldTranslationComposer;
use App\Form\Product\Fields\Renderer\ProductFieldsRenderer;
use App\Repository\LocaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProductFieldTranslationComposer::class)]
class ProductFieldTranslationComposerTest extends MockeryTestCase
{
    /** @var LocaleRepository&Mockery\MockInterface */
    private LocaleRepository $localeRepository;

    /** @var ProductFieldsRenderer&Mockery\MockInterface */
    private ProductFieldsRenderer $productFieldsRenderer;

    private ProductFieldTranslationComposer $composer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->localeRepository = \Mockery::mock(LocaleRepository::class);
        $this->productFieldsRenderer = \Mockery::mock(ProductFieldsRenderer::class);

        $this->composer = new ProductFieldTranslationComposer(
            $this->localeRepository,
            $this->productFieldsRenderer
        );
    }

    public function testComposeReturnsEmptyArrayWhenNoLocalesAvailable(): void
    {
        $product = \Mockery::mock(Product::class);
        $this->localeRepository->shouldReceive('getAvailableContentTranslationLocales')
            ->once()
            ->andReturn(new ArrayCollection([]));

        $result = $this->composer->compose($product);

        $this->assertSame([], $result);
    }

    public function testComposeReturnsFieldsWithTranslations(): void
    {
        $product = \Mockery::mock(Product::class);

        $localeEn = \Mockery::mock(Locale::class);
        $localeDe = \Mockery::mock(Locale::class);
        $locales = new ArrayCollection([$localeEn, $localeDe]);

        $this->localeRepository->shouldReceive('getAvailableContentTranslationLocales')
            ->once()
            ->andReturn($locales);

        $this->localeRepository->shouldReceive('extractLocaleIds')
            ->once()
            ->with($locales)
            ->andReturn(['en', 'de']);

        $fieldEn = TextField::new('name_en');
        $fieldDe = TextField::new('name_de');

        $this->productFieldsRenderer->shouldReceive('render')
            ->once()
            ->with($product, $localeEn)
            ->andReturn([$fieldEn]);

        $this->productFieldsRenderer->shouldReceive('render')
            ->once()
            ->with($product, $localeDe)
            ->andReturn([$fieldDe]);

        $result = $this->composer->compose($product);

        // Expected fields:
        // 0: FormField::addColumn(6)
        // 1: FormField::addFieldset(...)
        // 2: LocaleField::new('translation_locale', ...)
        // 3: TextField::new('name_en')
        // 4: TextField::new('name_de')

        $this->assertCount(5, $result);
        $this->assertInstanceOf(FormField::class, $result[0]);
        $this->assertInstanceOf(FormField::class, $result[1]);
        $this->assertInstanceOf(LocaleField::class, $result[2]);
        $this->assertSame($fieldEn, $result[3]);
        $this->assertSame($fieldDe, $result[4]);

        /** @var LocaleField $localeField */
        $localeField = $result[2];
        $dto = $localeField->getAsDto();

        // Checking LocaleField configuration via its attributes/options
        $this->assertStringContainsString('translation-locale', $dto->getCssClass());
        $this->assertSame('content-translation', $dto->getHtmlAttributes()['data-controller'] ?? '');
        $this->assertSame('change->content-translation#update', $dto->getHtmlAttributes()['data-action'] ?? '');
    }
}
