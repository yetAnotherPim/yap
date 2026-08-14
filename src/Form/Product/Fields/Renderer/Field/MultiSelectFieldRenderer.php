<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Renderer\Registry\FieldRendererInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

final class MultiSelectFieldRenderer implements FieldRendererInterface
{
    public function supports(FieldType $type): bool
    {
        return InputType::MultiSelect === $type->getInputType();
    }

    public function render(Field $field, ProductTypeFieldConfiguration $configuration): FieldInterface
    {
        return ChoiceField::new('field_'.$field->getType()->getCode())
            ->setFormTypeOptions([
                'multiple' => true,
                'label' => $field->getType()->getTitle(),
                'data' => (null !== $field->getValue()) ? explode(',', $field->getValue()) : [],
                'choices' => array_combine($configuration->getOptions(), $configuration->getOptions()),
                'mapped' => false,
                'required' => $configuration->isRequired(),
            ]);
    }
}
