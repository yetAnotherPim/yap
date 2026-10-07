<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer\Field;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Renderer\Field\Trait\TranslationTrait;
use App\Form\Product\Fields\Renderer\Registry\BatchEditFieldRendererInterface;
use App\Form\Product\Fields\Renderer\Registry\FieldRendererInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

final class TextEditorFieldRenderer implements FieldRendererInterface, BatchEditFieldRendererInterface
{
    use TranslationTrait;

    public function supports(FieldType $type): bool
    {
        return InputType::TextEditor === $type->getInputType();
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function render(Field $field, ProductTypeFieldConfiguration $configuration, string $label, ?Locale $locale = null): FieldInterface
    {
        $element = TextEditorField::new('field_'.$field->getType()->getCode().$this->getTranslationSuffix($locale))
            ->setFormTypeOptions([
                'label' => $label,
                'data' => InputType::castValue($this->getFieldValue($field, $configuration, $locale), InputType::TextEditor),
                'mapped' => false,
                'required' => null === $locale && $configuration->isRequired(),
            ]);

        return $this->addTranslationOptions($element, $field, $locale);
    }

    public function getBatchEditFormType(
        FieldType $fieldType,
    ): string {
        return TextareaType::class;
    }
}
