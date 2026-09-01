<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer\Field;

use App\Entity\Locale\Locale;
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

    /**
     * @throws \DateMalformedStringException
     */
    public function render(Field $field, ProductTypeFieldConfiguration $configuration, string $label, ?Locale $locale = null): FieldInterface
    {
        $element = ChoiceField::new('field_'.$field->getType()->getCode().(null !== $locale ? '-'.$locale->getId() : ''))
            ->setFormTypeOptions([
                'multiple' => true,
                'label' => $label,
                'data' => InputType::castValue($field->getValue(), InputType::MultiSelect),
                'choices' => array_combine($configuration->getOptions(), $configuration->getOptions()),
                'mapped' => false,
                'required' => $configuration->isRequired(),
            ]);

        if (null !== $locale) {
            $element->setFormTypeOption('row_attr', [
                'data-content-translation-locale' => $locale->getId(),
                'hidden' => true,
            ]);
        }

        return $element;
    }
}
