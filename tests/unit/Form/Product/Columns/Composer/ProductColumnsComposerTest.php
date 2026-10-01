<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Columns\Composer;

use App\Entity\Product\Field\Type\FieldType;
use App\Entity\User\Configuration\ProductColumnConfiguration;
use App\Entity\User\User;
use App\Form\Product\Columns\Composer\ProductColumnsComposer;
use App\Form\Product\Columns\Renderer\ColumnValueRenderer;
use App\Repository\ProductColumnConfigurationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(ProductColumnsComposer::class)]
class ProductColumnsComposerTest extends MockeryTestCase
{
    /** @var ProductColumnConfigurationRepository&MockInterface */
    private ProductColumnConfigurationRepository $productColumnConfigurationRepository;

    /** @var TranslatorInterface&MockInterface */
    private TranslatorInterface $translator;

    /** @var ColumnValueRenderer&MockInterface */
    private ColumnValueRenderer $columnValueRenderer;

    private ProductColumnsComposer $composer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productColumnConfigurationRepository = \Mockery::mock(
            ProductColumnConfigurationRepository::class,
        );

        $this->translator = \Mockery::mock(
            TranslatorInterface::class,
        );

        $this->columnValueRenderer = \Mockery::mock(
            ColumnValueRenderer::class,
        );

        $this->composer = new ProductColumnsComposer(
            $this->productColumnConfigurationRepository,
            $this->translator,
            $this->columnValueRenderer,
        );
    }

    public function testComposeReturnsEmptyArrayForNullUser(): void
    {
        $this->productColumnConfigurationRepository
            ->shouldNotReceive('getColumnsForUser');

        $fields = $this->composer->compose(null);

        $this->assertSame([], $fields);
    }

    public function testConfigureFieldsForIndexPageReturnsExpectedFields(): void
    {
        $user = \Mockery::mock(User::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $configuration = \Mockery::mock(ProductColumnConfiguration::class);

        $fieldTypeId = Uuid::v4();

        $this->translator
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('en');

        $fieldType
            ->shouldReceive('getId')
            ->once()
            ->andReturn($fieldTypeId);

        $fieldType
            ->shouldReceive('getTranslatedTitleByLocale')
            ->once()
            ->with('en')
            ->andReturn('Custom field');

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $this->productColumnConfigurationRepository
            ->shouldReceive('getColumnsForUser')
            ->once()
            ->with($user)
            ->andReturn(new ArrayCollection([$fieldTypeId->toString() => $configuration]));

        $fields = $this->composer->compose($user);

        $this->assertCount(5, $fields);

        $this->assertInstanceOf(CollectionField::class, $fields[0]);
        $this->assertEquals('identifiers', $fields[0]->getAsDto()->getProperty());
        $this->assertInstanceOf(AssociationField::class, $fields[1]);
        $this->assertEquals('productType', $fields[1]->getAsDto()->getProperty());
        $this->assertInstanceOf(TextField::class, $fields[2]);
        $this->assertSame(
            'field_'.str_replace('-', '_', (string) $fieldTypeId),
            $fields[2]->getAsDto()->getProperty(),
        );
        $this->assertInstanceOf(DateTimeField::class, $fields[3]);
        $this->assertEquals('createdAt', $fields[3]->getAsDto()->getProperty());
        $this->assertInstanceOf(DateTimeField::class, $fields[4]);
        $this->assertEquals('updatedAt', $fields[4]->getAsDto()->getProperty());
    }

    public function testComposeQueryReturnsQueryBuilderWhenCurrentUserIsNotUser(): void
    {
        $queryBuilder = \Mockery::mock(QueryBuilder::class);
        $searchDto = $this->createSearchDto();

        $this->productColumnConfigurationRepository
            ->shouldNotReceive('getColumnsForUser');

        $result = $this->composer->composeQuery(
            $queryBuilder,
            $searchDto,
            null
        );

        $this->assertSame($queryBuilder, $result);
    }

    public function testComposeQueryAddsJoinForConfiguredColumn(): void
    {
        $user = \Mockery::mock(User::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $configuration = \Mockery::mock(ProductColumnConfiguration::class);
        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $fieldTypeId = Uuid::v4();
        $alias = 'field_'.str_replace('-', '_', (string) $fieldTypeId);
        $joinAlias = 'index_'.$alias;

        $fieldType
            ->shouldReceive('getId')
            ->andReturn($fieldTypeId);

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $this->productColumnConfigurationRepository
            ->shouldReceive('getColumnsForUser')
            ->once()
            ->with($user)
            ->andReturn(new ArrayCollection([$fieldTypeId->toString() => $configuration]));

        $queryBuilder
            ->shouldReceive('leftJoin')
            ->once()
            ->with(
                'entity.fields',
                $joinAlias,
                'WITH',
                $joinAlias.'.type = :'.$alias.'_type',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with($alias.'_type', $fieldType)
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('addSelect')
            ->once()
            ->with($joinAlias)
            ->andReturnSelf();

        $searchDto = $this->createSearchDto();

        $result = $this->composer->composeQuery(
            $queryBuilder,
            $searchDto,
            $user,
        );

        $this->assertSame($queryBuilder, $result);
    }

    public function testComposeQuerySortsByConfiguredColumn(): void
    {
        $user = \Mockery::mock(User::class);
        $fieldType = \Mockery::mock(FieldType::class);
        $configuration = \Mockery::mock(ProductColumnConfiguration::class);
        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $fieldTypeId = Uuid::v4();
        $alias = 'field_'.str_replace('-', '_', (string) $fieldTypeId);
        $joinAlias = 'index_'.$alias;

        $fieldType
            ->shouldReceive('getId')
            ->andReturn($fieldTypeId);

        $configuration
            ->shouldReceive('getFieldType')
            ->once()
            ->andReturn($fieldType);

        $this->productColumnConfigurationRepository
            ->shouldReceive('getColumnsForUser')
            ->once()
            ->with($user)
            ->andReturn(new ArrayCollection([$fieldTypeId->toString() => $configuration]));

        $queryBuilder
            ->shouldReceive('leftJoin')
            ->once()
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('addSelect')
            ->once()
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('resetDQLPart')
            ->once()
            ->with('orderBy');

        $queryBuilder
            ->shouldReceive('addOrderBy')
            ->once()
            ->with($joinAlias.'.value', 'DESC');

        $searchDto = $this->createSearchDto(
            sort: [$alias => 'DESC'],
        );

        $result = $this->composer->composeQuery(
            $queryBuilder,
            $searchDto,
            $user,
        );

        $this->assertSame($queryBuilder, $result);
    }

    public function testComposeQuerySortsByProductProperty(): void
    {
        $user = \Mockery::mock(User::class);
        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $this->productColumnConfigurationRepository
            ->shouldReceive('getColumnsForUser')
            ->once()
            ->with($user)
            ->andReturn(new ArrayCollection([]));

        $queryBuilder
            ->shouldReceive('resetDQLPart')
            ->once()
            ->with('orderBy');

        $queryBuilder
            ->shouldReceive('addOrderBy')
            ->once()
            ->with('entity.createdAt', 'ASC');

        $searchDto = $this->createSearchDto(
            sort: ['createdAt' => 'ASC'],
        );

        $result = $this->composer->composeQuery(
            $queryBuilder,
            $searchDto,
            $user,
        );

        $this->assertSame($queryBuilder, $result);
    }

    /**
     * @param array<string, 'ASC'|'DESC'> $sort
     */
    private function createSearchDto(
        array $sort = [],
    ): SearchDto {
        return new SearchDto(
            new Request(),
            null,
            null,
            [],
            $sort,
            null,
        );
    }
}
