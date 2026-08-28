<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer\Field;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Renderer\Registry\FieldRendererInterface;
use App\Form\Product\Fields\Resolver\Enum\FileType;
use App\Form\Product\Fields\Resolver\FileResolver;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

final class ImageFieldRenderer implements FieldRendererInterface
{
    public function __construct(
        private readonly FileResolver $fileResolver,
    ) {
    }

    public function supports(FieldType $type): bool
    {
        return InputType::Image === $type->getInputType();
    }

    public function render(Field $field, ProductTypeFieldConfiguration $configuration, string $label): FieldInterface
    {
        return ImageField::new('field_'.$field->getType()->getCode())
            ->setUploadDir($this->fileResolver->getUploadDirectory(FileType::IMAGE))
            ->setUploadedFileNamePattern('[YYYY]/[MM]/[DD]/[slug]-[contenthash].[extension]')
            ->setFormTypeOptions([
                'label' => $label,
                'data' => $this->fileResolver->resolve($field->getValue(), FileType::IMAGE),
                'mapped' => false,
                'required' => $configuration->isRequired(),
            ]);
    }
}
