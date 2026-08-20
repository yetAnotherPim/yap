<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Renderer\Registry\FieldRendererInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;

final class ColorFieldRenderer implements FieldRendererInterface
{
    public function supports(FieldType $type): bool
    {
        return InputType::Color === $type->getInputType();
    }

    public function render(Field $field, ProductTypeFieldConfiguration $configuration): FieldInterface
    {
        return ColorField::new('field_'.$field->getType()->getCode())
            ->showValue()
            ->setFormTypeOptions([
                'label' => $field->getType()->getTitle(),
                'data' => InputType::castValue($field->getValue(), InputType::Color),
                'mapped' => false,
                'required' => $configuration->isRequired(),
            ]);
    }
}
