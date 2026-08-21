<?php

declare(strict_types=1);

namespace App\Entity\Product\Field\Type\Enum;

use DateTime;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum InputType: string implements TranslatableInterface
{
    case Section = 'section';
    case CheckBox = 'checkbox';
    case String = 'string';
    case Text = 'text';
    case Integer = 'integer';
    case Date = 'date';
    case DateTime = 'datetime';
    case Time = 'time';
    case Number = 'number';
    case Color = 'color';
    case Country = 'country';
    case Telephone = 'telephone';
    case Email = 'email';
    case Language = 'language';
    case Locale = 'locale';
    case TextEditor = 'texteditor';
    case Url = 'url';
    case Select = 'select';
    case MultiSelect = 'multiselect';
    case File = 'file';
    case Image = 'image';

    public function trans(
        TranslatorInterface $translator,
        ?string $locale = null,
    ): string {
        return $translator->trans(
            'field_type.input_type.'.$this->value,
            [],
            'admin',
            $locale,
        );
    }

    public static function getTypedValue(?string $value, InputType $inputType): string|int|bool|float|null
    {
        if (true === \is_string($value) && InputType::CheckBox === $inputType) {
            return (bool) $value;
        }

        if (true === \is_string($value) && InputType::Number === $inputType) {
            return (float) $value;
        }

        if (true === \is_string($value) && InputType::Integer === $inputType) {
            return (int) $value;
        }

        return $value;
    }

    /**
     * @return \DateTime|string[]|string|bool|float|null
     *
     * @throws \DateMalformedStringException
     */
    public static function castValue(?string $value, InputType $inputType, ?string $separator = ','): \DateTime|array|string|bool|float|null
    {
        if (InputType::CheckBox === $inputType) {
            return (bool) $value;
        }

        if (InputType::Date === $inputType || InputType::DateTime === $inputType || InputType::Time === $inputType) {
            return (false === empty($value)) ? new \DateTime($value) : null;
        }

        if (InputType::MultiSelect === $inputType) {
            /** @phpstan-var non-empty-string $separator */
            return (null !== $value && false === empty($separator)) ? explode($separator, $value) : [];
        }

        if (InputType::Number === $inputType) {
            return (null !== $value) ? self::parseDecimalValue($value, $separator) : null;
        }

        return $value;
    }

    private static function parseDecimalValue(string $value, ?string $decimalSeparator): float
    {
        if (null === $decimalSeparator || true === empty($decimalSeparator)) {
            return (float) $value;
        }

        return (float) str_replace($decimalSeparator, '.', $value);
    }
}
