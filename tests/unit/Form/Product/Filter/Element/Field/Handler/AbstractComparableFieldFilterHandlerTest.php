<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Filter\Element\Field\Handler;

use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\Filter\Element\Field\Handler\AbstractComparableFieldFilterHandler;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(AbstractComparableFieldFilterHandler::class)]
class AbstractComparableFieldFilterHandlerTest extends MockeryTestCase
{
    #[DataProvider('comparisonProvider')]
    public function testApply(
        string $comparison,
        string $operator,
    ): void {
        $fieldType = \Mockery::mock(FieldType::class);
        $filterDataDto = $this->createFilterDataDto(
            'foo',
            $comparison,
        );

        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $queryBuilder
            ->shouldReceive('andWhere')
            ->once()
            ->with('field.value '.$operator.' :field_test_0')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with('field_test_0', 'normalized_foo')
            ->andReturnSelf();

        $this->createHandler()->apply(
            $queryBuilder,
            $filterDataDto,
            $fieldType,
            'field.value',
        );
    }

    public function testApplyBetween(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $filterDataDto = $this->createFilterDataDto(
            'foo',
            ComparisonType::BETWEEN,
            'bar',
        );

        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $queryBuilder
            ->shouldReceive('andWhere')
            ->once()
            ->with(
                'field.value BETWEEN :field_test_0_from AND :field_test_0_to',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with(
                'field_test_0_from',
                'normalized_foo',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with(
                'field_test_0_to',
                'normalized_bar',
            )
            ->andReturnSelf();

        $this->createHandler()->apply(
            $queryBuilder,
            $filterDataDto,
            $fieldType,
            'field.value',
        );
    }

    public function testApplyThrowsExceptionForUnsupportedComparison(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $filterDataDto = $this->createFilterDataDto(
            'foo',
            'unsupported',
        );

        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(
            'Unsupported comparison "unsupported".',
        );

        $this->createHandler()->apply(
            $queryBuilder,
            $filterDataDto,
            $fieldType,
            'field.value',
        );
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function comparisonProvider(): iterable
    {
        yield 'equal' => [
            ComparisonType::EQ,
            '=',
        ];

        yield 'not equal' => [
            ComparisonType::NEQ,
            '<>',
        ];

        yield 'greater than' => [
            ComparisonType::GT,
            '>',
        ];

        yield 'greater than or equal' => [
            ComparisonType::GTE,
            '>=',
        ];

        yield 'less than' => [
            ComparisonType::LT,
            '<',
        ];

        yield 'less than or equal' => [
            ComparisonType::LTE,
            '<=',
        ];
    }

    private function createHandler(): AbstractComparableFieldFilterHandler
    {
        return new class extends AbstractComparableFieldFilterHandler {
            public function supports(FieldType $fieldType): bool
            {
                return true;
            }

            public function getFormType(): string
            {
                return \stdClass::class;
            }

            public function getFormTypeOptions(): array
            {
                return [];
            }

            protected function normalizeValue(
                mixed $value,
                FieldType $fieldType,
            ): mixed {
                if (!\is_string($value)) {
                    return null;
                }

                return 'normalized_'.$value;
            }
        };
    }

    private function createFilterDataDto(
        mixed $value,
        string $comparison = ComparisonType::EQ,
        mixed $value2 = null,
    ): FilterDataDto {
        $filterDto = new FilterDto();
        $filterDto->setProperty('field_test');

        return FilterDataDto::new(
            0,
            $filterDto,
            'entity',
            [
                'comparison' => $comparison,
                'value' => $value,
                'value2' => $value2,
            ],
        );
    }
}
