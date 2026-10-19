<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Filter\Element\Field;

use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\Filter\Element\Field\FieldFilter;
use App\Form\Product\Filter\Element\Field\Handler\FieldFilterHandlerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\TextFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Uid\Uuid;

#[CoversClass(FieldFilter::class)]
class FieldFilterTest extends MockeryTestCase
{
    public function testNew(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $fieldType
            ->shouldReceive('getId')
            ->once()
            ->andReturn(
                Uuid::fromString(
                    '123e4567-e89b-12d3-a456-426614174000',
                ),
            );

        $fieldType
            ->shouldReceive('getTranslatedTitleByLocale')
            ->once()
            ->with('de')
            ->andReturn('Titel');

        $handler = \Mockery::mock(
            FieldFilterHandlerInterface::class,
        );

        $handler
            ->shouldReceive('getFormType')
            ->once()
            ->andReturn(TextFilterType::class);

        $handler
            ->shouldReceive('getFormTypeOptions')
            ->once()
            ->andReturn([
                'foo' => 'bar',
            ]);

        $filter = FieldFilter::new(
            $fieldType,
            $handler,
            'de',
        );

        $filterDto = $filter->getAsDto();

        $this->assertSame(
            FieldFilter::class,
            $filterDto->getFqcn(),
        );

        $this->assertSame(
            'field_123e4567_e89b_12d3_a456_426614174000',
            $filterDto->getProperty(),
        );

        $this->assertSame(
            'Titel',
            $filterDto->getLabel(),
        );

        $this->assertSame(
            TextFilterType::class,
            $filterDto->getFormType(),
        );

        $formTypeOptions = $filterDto->getFormTypeOptions();

        $this->assertSame(
            'bar',
            $formTypeOptions['foo'],
        );
    }

    public function testApply(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);

        $fieldType
            ->shouldReceive('getId')
            ->once()
            ->andReturn(
                Uuid::fromString(
                    '123e4567-e89b-12d3-a456-426614174000',
                ),
            );

        $fieldType
            ->shouldReceive('getTranslatedTitleByLocale')
            ->once()
            ->with('de')
            ->andReturn('Titel');

        $handler = \Mockery::mock(
            FieldFilterHandlerInterface::class,
        );

        $handler
            ->shouldReceive('getFormType')
            ->once()
            ->andReturn(TextFilterType::class);

        $handler
            ->shouldReceive('getFormTypeOptions')
            ->once()
            ->andReturn([]);

        $filter = FieldFilter::new(
            $fieldType,
            $handler,
            'de',
        );

        $filterDataDto = $this->createFilterDataDto(
            'foo',
            ComparisonType::EQ,
        );

        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $queryBuilder
            ->shouldReceive('innerJoin')
            ->once()
            ->with(
                'entity.fields',
                'field_test_0_field',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('andWhere')
            ->once()
            ->with(
                'field_test_0_field.type = :field_test_0_type',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with(
                'field_test_0_type',
                $fieldType,
            )
            ->andReturnSelf();

        $handler
            ->shouldReceive('apply')
            ->once()
            ->with(
                $queryBuilder,
                $filterDataDto,
                $fieldType,
                'field_test_0_field.value',
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
     */
    private function createEntityDto(): EntityDto
    {
        /** @var EntityDto<object> $entityDto */
        $entityDto = (new \ReflectionClass(EntityDto::class))
            ->newInstanceWithoutConstructor();

        return $entityDto;
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
