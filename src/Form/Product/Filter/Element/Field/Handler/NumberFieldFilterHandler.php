<?php

declare(strict_types=1);

namespace App\Form\Product\Filter\Element\Field\Handler;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\NumericFilterType;

class NumberFieldFilterHandler extends AbstractComparableFieldFilterHandler
{
    public function supports(FieldType $fieldType): bool
    {
        return InputType::Number === $fieldType->getInputType();
    }

    public function getFormType(): string
    {
        return NumericFilterType::class;
    }

    public function getFormTypeOptions(): array
    {
        return [];
    }

    protected function normalizeValue(
        mixed $value,
        FieldType $fieldType,
    ): ?float {
        if (!\is_string($value) && !\is_int($value) && !\is_float($value)) {
            return null;
        }

        $value = (string) $value;

        if (',' === $fieldType->getDecimalSeparator()) {
            $value = str_replace(',', '.', $value);
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }
}
