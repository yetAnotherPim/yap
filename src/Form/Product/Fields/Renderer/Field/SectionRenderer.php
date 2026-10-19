<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer\Field;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Field\Section\SectionField;
use App\Form\Product\Fields\Renderer\Field\Trait\TranslationTrait;
use App\Form\Product\Fields\Renderer\Registry\FieldRendererInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;

final class SectionRenderer implements FieldRendererInterface
{
    use TranslationTrait;

    public function supports(FieldType $type): bool
    {
        return InputType::Section === $type->getInputType();
    }

    public function render(Field $field, ProductTypeFieldConfiguration $configuration, string $label, ?Locale $locale = null): FieldInterface
    {
        $element = SectionField::new('field_'.$field->getType()->getCode().$this->getTranslationSuffix($locale))
            ->setFormTypeOptions([
                'label' => $label,
                'mapped' => false,
            ]);

        return $this->addTranslationOptions($element, $field, $locale);
    }
}
