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
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

final class DateTimeFieldRenderer implements FieldRendererInterface, BatchEditFieldRendererInterface
{
    use TranslationTrait;

    public function supports(FieldType $type): bool
    {
        return InputType::DateTime === $type->getInputType();
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function render(Field $field, ProductTypeFieldConfiguration $configuration, string $label, ?Locale $locale = null): FieldInterface
    {
        $element = DateTimeField::new('field_'.$field->getType()->getCode().$this->getTranslationSuffix($locale))
            ->setFormTypeOptions([
                'label' => $label,
                'data' => InputType::castValue($this->getFieldValue($field, $configuration, $locale), InputType::DateTime),
                'mapped' => false,
                'required' => null === $locale && $configuration->isRequired(),
            ]);

        return $this->addTranslationOptions($element, $field, $locale);
    }

    public function getBatchEditFormType(): string
    {
        return DateTimeType::class;
    }

    public function getBatchEditOptions(): array
    {
        return [
            'input' => 'string',
        ];
    }
}
