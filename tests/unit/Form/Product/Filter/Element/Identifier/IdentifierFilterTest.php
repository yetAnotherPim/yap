<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Filter\Element\Identifier;

use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Form\Product\Filter\Element\Identifier\IdentifierFilter;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\TextFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Uid\Uuid;

#[CoversClass(IdentifierFilter::class)]
class IdentifierFilterTest extends MockeryTestCase
{
    public function testNew(): void
    {
        $id = Uuid::fromString(
            '01996b9d-15a2-7d10-a85a-18c99b8ed801',
        );

        $identifierType = \Mockery::mock(IdentifierType::class);
        $identifierType
            ->shouldReceive('getId')
            ->once()
            ->andReturn($id);
        $identifierType
            ->shouldReceive('getTitle')
            ->once()
            ->andReturn('EAN');

        $filter = IdentifierFilter::new($identifierType);

        $dto = $filter->getAsDto();

        $this->assertSame(
            'identifier_01996b9d_15a2_7d10_a85a_18c99b8ed801',
            $dto->getProperty(),
        );
        $this->assertSame('EAN', $dto->getLabel());
        $this->assertSame(
            TextFilterType::class,
            $dto->getFormType(),
        );
    }

    #[DataProvider('provideComparisons')]
    public function testApply(
        string $comparison,
        string $value,
        string $expectedOperator,
        string $expectedValue,
    ): void {
        $identifierType = \Mockery::mock(IdentifierType::class);
        $identifierType
            ->shouldReceive('getId')
            ->andReturn(
                Uuid::fromString(
                    '01996b9d-15a2-7d10-a85a-18c99b8ed801',
                ),
            );
        $identifierType
            ->shouldReceive('getTitle')
            ->andReturn('EAN');

        $filter = IdentifierFilter::new($identifierType);

        $filterDataDto = FilterDataDto::new(
            0,
            $filter->getAsDto(),
            'entity',
            [
                'comparison' => $comparison,
                'value' => $value,
            ],
        );

        $parameterName = $filterDataDto->getParameterName();
        $identifierAlias = $parameterName.'_identifier';

        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $queryBuilder
            ->shouldReceive('innerJoin')
            ->once()
            ->with(
                'entity.identifiers',
                $identifierAlias,
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('andWhere')
            ->once()
            ->with(
                $identifierAlias.'.type = :'.$parameterName.'_type',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with(
                $parameterName.'_type',
                $identifierType,
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('andWhere')
            ->once()
            ->with(
                $identifierAlias
                .'.value '
                .$expectedOperator
                .' :'
                .$parameterName,
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with(
                $parameterName,
                $expectedValue,
            )
            ->andReturnSelf();

        $filter->apply(
            $queryBuilder,
            $filterDataDto,
            null,
            $this->createEntityDto(),
        );
    }

    /**
     * @return iterable<string, array{string, string, string, string}>
     */
    public static function provideComparisons(): iterable
    {
        yield 'equals' => [
            ComparisonType::EQ,
            '123456',
            '=',
            '123456',
        ];

        yield 'not equals' => [
            ComparisonType::NEQ,
            '123456',
            '<>',
            '123456',
        ];

        yield 'contains' => [
            ComparisonType::CONTAINS,
            '123456',
            'LIKE',
            '%123456%',
        ];

        yield 'not contains' => [
            ComparisonType::NOT_CONTAINS,
            '123456',
            'NOT LIKE',
            '%123456%',
        ];

        yield 'starts with' => [
            ComparisonType::STARTS_WITH,
            '123456',
            'LIKE',
            '123456%',
        ];

        yield 'ends with' => [
            ComparisonType::ENDS_WITH,
            '123456',
            'LIKE',
            '%123456',
        ];
    }

    public function testApplyDoesNotApplyValueConditionForNonStringValue(): void
    {
        $identifierType = \Mockery::mock(IdentifierType::class);
        $identifierType
            ->shouldReceive('getId')
            ->andReturn(Uuid::v7());
        $identifierType
            ->shouldReceive('getTitle')
            ->andReturn('EAN');

        $filter = IdentifierFilter::new($identifierType);

        $filterDataDto = FilterDataDto::new(
            0,
            $filter->getAsDto(),
            'entity',
            [
                'comparison' => ComparisonType::EQ,
                'value' => null,
            ],
        );

        $parameterName = $filterDataDto->getParameterName();
        $identifierAlias = $parameterName.'_identifier';

        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $queryBuilder
            ->shouldReceive('innerJoin')
            ->once()
            ->with(
                'entity.identifiers',
                $identifierAlias,
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('andWhere')
            ->once()
            ->with(
                $identifierAlias.'.type = :'.$parameterName.'_type',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with(
                $parameterName.'_type',
                $identifierType,
            )
            ->andReturnSelf();

        $filter->apply(
            $queryBuilder,
            $filterDataDto,
            null,
            $this->createEntityDto(),
        );
    }

    public function testApplyThrowsExceptionForUnsupportedComparison(): void
    {
        $identifierType = \Mockery::mock(IdentifierType::class);
        $identifierType
            ->shouldReceive('getId')
            ->andReturn(Uuid::v7());
        $identifierType
            ->shouldReceive('getTitle')
            ->andReturn('EAN');

        $filter = IdentifierFilter::new($identifierType);

        $filterDataDto = FilterDataDto::new(
            0,
            $filter->getAsDto(),
            'entity',
            [
                'comparison' => 'unsupported',
                'value' => '123456',
            ],
        );

        $parameterName = $filterDataDto->getParameterName();
        $identifierAlias = $parameterName.'_identifier';

        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $queryBuilder
            ->shouldReceive('innerJoin')
            ->once()
            ->with(
                'entity.identifiers',
                $identifierAlias,
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('andWhere')
            ->once()
            ->with(
                $identifierAlias.'.type = :'.$parameterName.'_type',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with(
                $parameterName.'_type',
                $identifierType,
            )
            ->andReturnSelf();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(
            'Unsupported comparison "unsupported".',
        );

        $filter->apply(
            $queryBuilder,
            $filterDataDto,
            null,
            $this->createEntityDto(),
        );
    }

    /**
     * @return EntityDto<object>
     *
     * @throws \ReflectionException
     */
    private function createEntityDto(): EntityDto
    {
        return new \ReflectionClass(EntityDto::class)
            ->newInstanceWithoutConstructor();
    }
}
