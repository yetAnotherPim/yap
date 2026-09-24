<?php

declare(strict_types=1);

namespace App\Form\Product\Filter\Element\Field\Handler;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\DateTimeFilterType;

class DateFieldFilterHandler extends AbstractComparableFieldFilterHandler
{
    public function supports(FieldType $fieldType): bool
    {
        return InputType::Date === $fieldType->getInputType();
    }

    public function getFormType(): string
    {
        return DateTimeFilterType::class;
    }

    public function getFormTypeOptions(): array
    {
        return [];
    }

    protected function normalizeValue(mixed $value, FieldType $fieldType): ?string
    {
        if (!$value instanceof \DateTimeInterface) {
            return null;
        }

        return $value->format('Y-m-d');
    }
}
