<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Filter\Element\Field\Handler;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\Filter\Element\Field\Handler\BooleanFieldFilterHandler;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\BooleanFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BooleanFieldFilterHandler::class)]
class BooleanFieldFilterHandlerTest extends MockeryTestCase
{
    private BooleanFieldFilterHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new BooleanFieldFilterHandler();
    }

    public function testSupportsCheckBox(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn(InputType::CheckBox);

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
            BooleanFilterType::class,
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

    public function testApplyTrue(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $filterDataDto = $this->createFilterDataDto(true);

        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $queryBuilder
            ->shouldReceive('andWhere')
            ->once()
            ->with('field.value = :field_test_0')
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with('field_test_0', '1')
            ->andReturnSelf();

        $this->handler->apply(
            $queryBuilder,
            $filterDataDto,
            $fieldType,
            'field.value',
        );
    }

    public function testApplyFalse(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $filterDataDto = $this->createFilterDataDto(false);

        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $queryBuilder
            ->shouldReceive('andWhere')
            ->once()
            ->with(
                '(field.value IS NULL OR field.value = :field_test_0)',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with('field_test_0', '')
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
