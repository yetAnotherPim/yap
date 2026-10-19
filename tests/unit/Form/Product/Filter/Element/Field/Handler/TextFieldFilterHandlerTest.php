<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Filter\Element\Field\Handler;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Form\Product\Filter\Element\Field\Handler\TextFieldFilterHandler;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\TextFilterType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(TextFieldFilterHandler::class)]
class TextFieldFilterHandlerTest extends MockeryTestCase
{
    private TextFieldFilterHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = new TextFieldFilterHandler();
    }

    #[DataProvider('supportedInputTypeProvider')]
    public function testSupports(
        InputType $inputType,
    ): void {
        $fieldType = \Mockery::mock(FieldType::class);
        $fieldType
            ->shouldReceive('getInputType')
            ->once()
            ->andReturn($inputType);

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
            ->andReturn(InputType::Integer);

        $this->assertFalse(
            $this->handler->supports($fieldType),
        );
    }

    public function testGetFormType(): void
    {
        $this->assertSame(
            TextFilterType::class,
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

    #[DataProvider('comparisonProvider')]
    public function testApply(
        string $comparison,
        string $operator,
        string $expectedValue,
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
            ->with(
                'field.value '.$operator.' :field_test_0',
            )
            ->andReturnSelf();

        $queryBuilder
            ->shouldReceive('setParameter')
            ->once()
            ->with(
                'field_test_0',
                $expectedValue,
            )
            ->andReturnSelf();

        $this->handler->apply(
            $queryBuilder,
            $filterDataDto,
            $fieldType,
            'field.value',
        );
    }

    public function testApplyReturnsForNonStringValue(): void
    {
        $fieldType = \Mockery::mock(FieldType::class);
        $filterDataDto = $this->createFilterDataDto(
            123,
            ComparisonType::EQ,
        );

        $queryBuilder = \Mockery::mock(QueryBuilder::class);

        $this->handler->apply(
            $queryBuilder,
            $filterDataDto,
            $fieldType,
            'field.value',
        );

        $this->addToAssertionCount(1);
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

        $this->handler->apply(
            $queryBuilder,
            $filterDataDto,
            $fieldType,
            'field.value',
        );
    }

    /**
     * @return iterable<string, array{InputType}>
     */
    public static function supportedInputTypeProvider(): iterable
    {
        yield 'string' => [InputType::String];
        yield 'text' => [InputType::Text];
        yield 'text editor' => [InputType::TextEditor];
        yield 'email' => [InputType::Email];
        yield 'telephone' => [InputType::Telephone];
        yield 'url' => [InputType::Url];
        yield 'color' => [InputType::Color];
        yield 'country' => [InputType::Country];
        yield 'language' => [InputType::Language];
        yield 'locale' => [InputType::Locale];
        yield 'select' => [InputType::Select];
        yield 'multi select' => [InputType::MultiSelect];
        yield 'file' => [InputType::File];
        yield 'image' => [InputType::Image];
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function comparisonProvider(): iterable
    {
        yield 'equal' => [
            ComparisonType::EQ,
            '=',
            'foo',
        ];

        yield 'not equal' => [
            ComparisonType::NEQ,
            '<>',
            'foo',
        ];

        yield 'contains' => [
            ComparisonType::CONTAINS,
            'LIKE',
            '%foo%',
        ];

        yield 'not contains' => [
            ComparisonType::NOT_CONTAINS,
            'NOT LIKE',
            '%foo%',
        ];

        yield 'starts with' => [
            ComparisonType::STARTS_WITH,
            'LIKE',
            'foo%',
        ];

        yield 'ends with' => [
            ComparisonType::ENDS_WITH,
            'LIKE',
            '%foo',
        ];
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
