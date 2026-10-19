<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Filter\Element\Field;

use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\Filter\Element\Field\FieldTranslationFilter;
use App\Form\Product\Filter\Element\Field\Handler\FieldFilterHandlerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Uid\Uuid;

#[CoversClass(FieldTranslationFilter::class)]
final class FieldTranslationFilterTest extends MockeryTestCase
{
    public function testNew(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType
            ->shouldReceive('getId')
            ->once()
            ->andReturn(
                Uuid::fromString('123e4567-e89b-12d3-a456-426614174000'),
            );
        $fieldType
            ->shouldReceive('getTranslatedTitleByLocale')
            ->once()
            ->with('de')
            ->andReturn('Titel');

        $handler = \Mockery::mock(FieldFilterHandlerInterface::class);
        $handler
            ->shouldReceive('getFormType')
            ->once()
            ->andReturn(TextFilter::class);
        $handler
            ->shouldReceive('getFormTypeOptions')
            ->once()
            ->andReturn(['foo' => 'bar']);

        $filter = FieldTranslationFilter::new(
            $fieldType,
            $handler,
            'de',
        );

        $filterDto = $filter->getAsDto();

        $this->assertSame(
            FieldTranslationFilter::class,
            $filterDto->getFqcn(),
        );
        $this->assertSame(
            'field_translation_123e4567_e89b_12d3_a456_426614174000',
            $filterDto->getProperty(),
        );
        $this->assertSame('Titel', $filterDto->getLabel());
        $this->assertSame(TextFilter::class, $filterDto->getFormType());

        $formTypeOptions = $filterDto->getFormTypeOptions();

        $this->assertSame('bar', $formTypeOptions['foo']);
    }

    public function testApply(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType
            ->shouldReceive('getId')
            ->once()
            ->andReturn(
                Uuid::fromString('123e4567-e89b-12d3-a456-426614174000'),
            );
        $fieldType
            ->shouldReceive('getTranslatedTitleByLocale')
            ->once()
            ->with('de')
            ->andReturn('Titel');

        $handler = \Mockery::mock(FieldFilterHandlerInterface::class);
        $handler
            ->shouldReceive('getFormType')
            ->once()
            ->andReturn(TextFilter::class);
        $handler
            ->shouldReceive('getFormTypeOptions')
            ->once()
            ->andReturn([]);

        $filter = FieldTranslationFilter::new(
            $fieldType,
            $handler,
            'de',
        );

        $filterDataDto = $this->createFilterDataDto();

        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $queryBuilder
            ->shouldReceive('innerJoin')
            ->once()
            ->with(
                'entity.fields',
                'field_translation_test_0_field',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('innerJoin')
            ->once()
            ->with(
                'field_translation_test_0_field.translations',
                'field_translation_test_0_translation',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('andWhere')
            ->once()
            ->with(
                'field_translation_test_0_field.type = :field_translation_test_0_type',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with(
                'field_translation_test_0_type',
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
                'field_translation_test_0_translation.value',
            );

        $filter->apply(
            $queryBuilder,
            $filterDataDto,
            null,
            $this->createEntityDto(),
        );
    }

    private function createFilterDataDto(): FilterDataDto
    {
        $filterDto = new FilterDto();
        $filterDto->setProperty('field_translation_test');

        return FilterDataDto::new(
            0,
            $filterDto,
            'entity',
            [
                'comparison' => '=',
                'value' => 'foo',
                'value2' => null,
            ],
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
}
