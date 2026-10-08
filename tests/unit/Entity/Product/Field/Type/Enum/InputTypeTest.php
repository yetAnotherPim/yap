<?php

declare(strict_types=1);

namespace App\Tests\unit\Entity\Product\Field\Type\Enum;

use App\Entity\Product\Field\Type\Enum\InputType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(InputType::class)]
class InputTypeTest extends TestCase
{
    #[DataProvider('provideTypedValue')]
    public function testGetTypedValue(
        ?string $value,
        InputType $inputType,
        string|int|bool|float|null $expected,
    ): void {
        self::assertSame($expected, InputType::getTypedValue($value, $inputType));
    }

    #[DataProvider('provideCastableValues')]
    public function testCastValue(?string $value, InputType $inputType, ?string $separator, mixed $expected): void
    {
        $this->assertEquals($expected, InputType::castValue($value, $inputType, $separator));
    }

    public function testTrans(): void
    {
        $dataType = InputType::String;
        $translatorMock = \Mockery::mock(\Symfony\Contracts\Translation\TranslatorInterface::class);

        $translatorMock
            ->shouldReceive('trans')
            ->once()
            ->with('field_type.input_type.string', [], 'admin', 'de_DE')
            ->andReturn('Zeichenkette');

        $this->assertEquals('Zeichenkette', $dataType->trans($translatorMock, 'de_DE'));
    }

    /**
     * @return iterable<string, array{0: string|null, 1: InputType, 2:string|null, 3: \DateTime|string[]|string|int|bool|float|null}>
     */
    public static function provideCastableValues(): iterable
    {
        yield 'null' => [null, InputType::Text, null, null];
        yield 'text' => ['text', InputType::Text, null, 'text'];
        yield 'checkbox_null' => [null, InputType::CheckBox, null, false];
        yield 'checkbox_true' => ['1', InputType::CheckBox, null, true];
        yield 'datetime_null' => [null, InputType::DateTime, null, null];
        yield 'datetime' => ['2020-01-01 12:00:00', InputType::DateTime, null, new \DateTime('2020-01-01 12:00:00')];
        yield 'datetime_invalid' => ['sdf', InputType::DateTime, null, null];
        yield 'date_null' => [null, InputType::Date, null, null];
        yield 'date' => ['2020-01-01 12:00:00', InputType::Date, null, new \DateTime('2020-01-01 12:00:00')];
        yield 'time_null' => [null, InputType::Time, null, null];
        yield 'time' => ['2020-01-01 12:00:00', InputType::Time, null, new \DateTime('2020-01-01 12:00:00')];
        yield 'multiselect_null' => [null, InputType::MultiSelect, null, []];
        yield 'multiselect_empty_separator' => ['some value', InputType::MultiSelect, '', []];
        yield 'multiselect' => ['a,b,c', InputType::MultiSelect, ',', ['a', 'b', 'c']];
        yield 'number_null' => [null, InputType::Number, ',', null];
        yield 'number_empty_separator' => ['7,97', InputType::Number, '', 7];
        yield 'number_separator' => ['7,97', InputType::Number, ',', 7.97];
        yield 'integer_null' => [null, InputType::Integer, ',', null];
        yield 'integer_string' => ['sdf', InputType::Integer, ',', 0];
        yield 'integer_float' => ['7.97', InputType::Integer, ',', 7];
        yield 'integer' => ['7', InputType::Integer, ',', 7];
    }

    /**
     * @return iterable<string, array{value: string|null, inputType: InputType, expected: string|int|bool|float|null}>
     */
    public static function provideTypedValue(): iterable
    {
        yield 'null' => [
            'value' => null,
            'inputType' => InputType::Text,
            'expected' => null,
        ];

        yield 'checkbox true' => [
            'value' => '1',
            'inputType' => InputType::CheckBox,
            'expected' => true,
        ];

        yield 'checkbox false' => [
            'value' => '0',
            'inputType' => InputType::CheckBox,
            'expected' => false,
        ];

        yield 'number' => [
            'value' => '3.14159265359',
            'inputType' => InputType::Number,
            'expected' => 3.14159265359,
        ];

        yield 'integer' => [
            'value' => '42',
            'inputType' => InputType::Integer,
            'expected' => 42,
        ];

        yield 'string' => [
            'value' => 'Something',
            'inputType' => InputType::Text,
            'expected' => 'Something',
        ];

        yield 'empty string' => [
            'value' => '',
            'inputType' => InputType::Text,
            'expected' => '',
        ];
    }
}
