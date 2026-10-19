<?php

declare(strict_types=1);

namespace App\Form\Product\Filter\Element\Field\Handler;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\NumericFilterType;

class IntegerFieldFilterHandler extends AbstractComparableFieldFilterHandler
{
    public function supports(FieldType $fieldType): bool
    {
        return InputType::Integer === $fieldType->getInputType();
    }

    public function getFormType(): string
    {
        return NumericFilterType::class;
    }

    public function getFormTypeOptions(): array
    {
        return [];
    }

    protected function normalizeValue(mixed $value, FieldType $fieldType): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }
}
