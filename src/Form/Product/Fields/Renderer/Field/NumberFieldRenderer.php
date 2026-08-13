<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Renderer\Registry\FieldRendererInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

final class NumberFieldRenderer implements FieldRendererInterface
{
    public function supports(FieldType $type): bool
    {
        return InputType::Number === $type->getInputType();
    }

    public function render(Field $field, ProductTypeFieldConfiguration $configuration): FieldInterface
    {
        return NumberField::new('field_'.$field->getType()->getCode())
            ->setDecimalSeparator($field->getType()->getDecimalSeparator())
            ->setFormTypeOptions([
                'label' => $field->getType()->getTitle(),
                'data' => (null !== $field->getValue()) ? $this->parseDecimalValue($field->getValue(), $field->getType()->getDecimalSeparator()) : null,
                'mapped' => false,
                'required' => $configuration->isRequired(),
            ]);
    }

    private function parseDecimalValue(string $value, string $decimalSeparator): float
    {
        return (float) str_replace($decimalSeparator, '.', $value);
    }
}
