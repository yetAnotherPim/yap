<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Filter\Element\Field\Handler;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\Filter\Element\Field\Handler\AbstractComparableFieldFilterHandler;
use App\Form\Product\Filter\Element\Field\Handler\IntegerFieldFilterHandler;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\NumericFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IntegerFieldFilterHandler::class)]
#[CoversClass(AbstractComparableFieldFilterHandler::class)]
class IntegerFieldFilterHandlerTest extends MockeryTestCase
{
    private IntegerFieldFilterHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new IntegerFieldFilterHandler();
    }

    public function testSupportsInteger(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::Integer);

        $this->assertTrue(
            $this->handler->supports($fieldType),
        );
    }

    public function testDoesNotSupportOtherInputType(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::String);

        $this->assertFalse(
            $this->handler->supports($fieldType),
        );
    }

    public function testGetFormType(): void
    {
        $this->assertSame(
            NumericFilterType::class,
            $this->handler->getFormType(),
        );
    }

    public function testGetFormTypeOptions(): void
    {
        $this->assertSame(
            [],
            $this->handler->getFormTypeOptions(),
        );
    }

    public function testApplyNormalizesInteger(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $filterDataDto = $this->createFilterDataDto(
            42.0,
            ComparisonType::EQ,
        );

        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $queryBuilder
            ->shouldReceive('andWhere')
            ->once()
            ->with('field.value = :field_test_0')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with('field_test_0', 42)
            ->andReturnSelf();

        $this->handler->apply(
            $queryBuilder,
            $filterDataDto,
            $fieldType,
            'field.value',
        );
    }

    public function testApplyNormalizesInvalidValueToNull(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $filterDataDto = $this->createFilterDataDto(
            'invalid',
            ComparisonType::EQ,
        );

        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $queryBuilder
            ->shouldReceive('andWhere')
            ->once()
            ->with('field.value = :field_test_0')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with('field_test_0', null)
            ->andReturnSelf();

        $this->handler->apply(
            $queryBuilder,
            $filterDataDto,
            $fieldType,
            'field.value',
        );
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
