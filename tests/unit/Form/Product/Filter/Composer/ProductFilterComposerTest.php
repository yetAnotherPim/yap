<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Filter\Composer;

use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Entity\Product\Type\ProductType;
use App\Form\Product\Filter\Composer\ProductFilterComposer;
use App\Form\Product\Filter\Element\Category\FilterCategory;
use App\Form\Product\Filter\Element\Field\FieldFilter;
use App\Form\Product\Filter\Element\Field\Handler\FieldFilterHandlerInterface;
use App\Form\Product\Filter\Element\Field\Registry\FieldFilterHandlerRegistry;
use App\Form\Product\Filter\Element\Identifier\IdentifierExistsFilter;
use App\Form\Product\Filter\Element\Identifier\IdentifierFilter;
use App\Repository\FieldTypeRepository;
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
#[UsesClass(FilterCategory::class)]
#[UsesClass(FieldFilter::class)]
#[UsesClass(IdentifierFilter::class)]
#[UsesClass(IdentifierExistsFilter::class)]
class ProductFilterComposerTest extends MockeryTestCase
{
    /** @var IdentifierTypeRepository&MockInterface */
    private IdentifierTypeRepository $identifierTypeRepository;

    /** @var FieldTypeRepository&MockInterface */
    private FieldTypeRepository $fieldTypeRepository;

    /** @var TranslatorInterface&MockInterface */
    private TranslatorInterface $translator;

    /** @var FieldFilterHandlerRegistry&MockInterface */
    private FieldFilterHandlerRegistry $fieldFilterHandlerRegistry;

    private ProductFilterComposer $composer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->identifierTypeRepository = \Mockery::mock(IdentifierTypeRepository::class);
        $this->translator = \Mockery::mock(TranslatorInterface::class);
        $this->fieldTypeRepository = \Mockery::mock(FieldTypeRepository::class);
        $this->fieldFilterHandlerRegistry = \Mockery::mock(FieldFilterHandlerRegistry::class);

        $this->composer = new ProductFilterComposer(
            $this->identifierTypeRepository,
            $this->fieldTypeRepository,
            $this->translator,
            $this->fieldFilterHandlerRegistry,
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

        $fieldType = \Mockery::mock(FieldType::class);
        $fieldFilterHandler = \Mockery::mock(FieldFilterHandlerInterface::class);

        $this->translator
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('de_DE');

        $fieldType
            ->shouldReceive('getId')
            ->once()
            ->andReturn(Uuid::v7());

        $fieldType
            ->shouldReceive('getTranslatedTitleByLocale')
            ->once()
            ->with('de_DE')
            ->andReturn('translated title');

        $fieldFilterHandler
            ->shouldReceive('getFormType')
            ->once();

        $fieldFilterHandler
            ->shouldReceive('getFormTypeOptions')
            ->once();

        $this->fieldTypeRepository
            ->shouldReceive('findBy')
            ->once()
            ->with(['filterable' => true])
            ->andReturn([$fieldType]);

        $this->fieldFilterHandlerRegistry
            ->shouldReceive('get')
            ->once()
            ->with(\Mockery::type(FieldType::class))
            ->andReturn($fieldFilterHandler);

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

        $this->fieldTypeRepository
            ->shouldReceive('findBy')
            ->once()
            ->with(['filterable' => true])
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
