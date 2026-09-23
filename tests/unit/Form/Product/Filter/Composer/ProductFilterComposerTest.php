<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Filter\Composer;

use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Entity\Product\Type\ProductType;
use App\Form\Product\Filter\Composer\ProductFilterComposer;
use App\Form\Product\Filter\Identifier\IdentifierExistsFilter;
use App\Form\Product\Filter\Identifier\IdentifierFilter;
use App\Repository\IdentifierTypeRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(ProductFilterComposer::class)]
#[UsesClass(IdentifierFilter::class)]
#[UsesClass(IdentifierExistsFilter::class)]
class ProductFilterComposerTest extends MockeryTestCase
{
    /** @var IdentifierTypeRepository&MockInterface */
    private IdentifierTypeRepository $identifierTypeRepository;

    /** @var TranslatorInterface&MockInterface */
    private TranslatorInterface $translator;

    private ProductFilterComposer $composer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->identifierTypeRepository = \Mockery::mock(
            IdentifierTypeRepository::class,
        );
        $this->translator = \Mockery::mock(
            TranslatorInterface::class,
        );

        $this->composer = new ProductFilterComposer(
            $this->identifierTypeRepository,
            $this->translator,
        );
    }

    public function testComposeAddsExpectedFilters(): void
    {
        $identifierType1 = \Mockery::mock(IdentifierType::class);
        $identifierType1
            ->shouldReceive('getId')
            ->andReturn(Uuid::v7());

        $identifierType1
            ->shouldReceive('getTitle')
            ->andReturn('EAN');

        $identifierType2 = \Mockery::mock(IdentifierType::class);
        $identifierType2
            ->shouldReceive('getId')
            ->andReturn(Uuid::v7());
        $identifierType2
            ->shouldReceive('getTitle')
            ->andReturn('SKU');

        $this->identifierTypeRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn([
                $identifierType1,
                $identifierType2,
            ]);

        $filters = $this->composer->compose(
            Filters::new(),
        );

        $filtersDto = $filters->getAsDto();

        $this->assertInstanceOf(
            EntityFilter::class,
            $filtersDto->getFilter('productType'),
        );

        $this->assertInstanceOf(
            DateTimeFilter::class,
            $filtersDto->getFilter('updatedAt'),
        );

        $this->assertInstanceOf(
            DateTimeFilter::class,
            $filtersDto->getFilter('createdAt'),
        );

        $identifierType1Id = str_replace(
            '-',
            '_',
            (string) $identifierType1->getId(),
        );

        $this->assertInstanceOf(
            IdentifierExistsFilter::class,
            $filtersDto->getFilter(
                'has_identifier_'.$identifierType1Id,
            ),
        );

        $this->assertInstanceOf(
            IdentifierFilter::class,
            $filtersDto->getFilter(
                'identifier_'.$identifierType1Id,
            ),
        );

        $identifierType2Id = str_replace(
            '-',
            '_',
            (string) $identifierType2->getId(),
        );

        $this->assertInstanceOf(
            IdentifierExistsFilter::class,
            $filtersDto->getFilter(
                'has_identifier_'.$identifierType2Id,
            ),
        );

        $this->assertInstanceOf(
            IdentifierFilter::class,
            $filtersDto->getFilter(
                'identifier_'.$identifierType2Id,
            ),
        );
    }

    public function testComposeConfiguresProductTypeChoiceLabel(): void
    {
        $this->identifierTypeRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn([]);

        $this->translator
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('de_DE');

        $productType = \Mockery::mock(ProductType::class);
        $productType
            ->shouldReceive('getTranslatedTitleByLocale')
            ->once()
            ->with('de_DE')
            ->andReturn('Bekleidung');

        $filters = $this->composer->compose(
            Filters::new(),
        );

        $productTypeFilter = $filters
            ->getAsDto()
            ->getFilter('productType');

        $this->assertInstanceOf(
            EntityFilter::class,
            $productTypeFilter,
        );

        $formTypeOptions = $productTypeFilter
            ->getAsDto()
            ->getFormTypeOptions();

        $this->assertArrayHasKey(
            'value_type_options',
            $formTypeOptions,
        );

        $this->assertIsArray(
            $formTypeOptions['value_type_options'],
        );

        $this->assertArrayHasKey(
            'choice_label',
            $formTypeOptions['value_type_options'],
        );

        $choiceLabel = $formTypeOptions['value_type_options']['choice_label'];

        $this->assertIsCallable($choiceLabel);

        $this->assertSame(
            'Bekleidung',
            $choiceLabel($productType),
        );
    }
}
