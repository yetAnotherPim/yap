<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Renderer\Registry\FieldRendererInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FileField;
use EasyCorp\Bundle\EasyAdminBundle\Field\LocaleField;

final class FileFieldRenderer implements FieldRendererInterface
{
    public function supports(FieldType $type): bool
    {
        return InputType::File === $type->getInputType();
    }

    public function render(Field $field, ProductTypeFieldConfiguration $configuration): FieldInterface
    {
        return FileField::new('field_'.$field->getType()->getCode())
            ->setUploadDir('public/uploads/files')
            ->setUploadedFileNamePattern('[YYYY]/[MM]/[DD]/[slug]-[contenthash].[extension]')
            ->setFormTypeOptions([
                'label' => $field->getType()->getTitle(),
                'data' => $field->getValue(),
                'mapped' => false,
                'required' => $configuration->isRequired(),
            ]);
    }
}
