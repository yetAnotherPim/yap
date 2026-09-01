<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer\Field;

use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Form\Product\Fields\Renderer\Registry\FieldRendererInterface;
use App\Form\Product\Fields\Resolver\Enum\FileType;
use App\Form\Product\Fields\Resolver\FileResolver;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FileField;

final class FileFieldRenderer implements FieldRendererInterface
{
    public function __construct(
        private readonly FileResolver $fileResolver,
    ) {
    }

    public function supports(FieldType $type): bool
    {
        return InputType::File === $type->getInputType();
    }

    public function render(Field $field, ProductTypeFieldConfiguration $configuration, string $label, ?Locale $locale = null): FieldInterface
    {
        $element = FileField::new('field_'.$field->getType()->getCode().(null !== $locale ? '-'.$locale->getId() : ''))
            ->setUploadDir($this->fileResolver->getUploadDirectory(FileType::FILE))
            ->setUploadedFileNamePattern('[YYYY]/[MM]/[DD]/[slug]-[contenthash].[extension]')
            ->setFormTypeOptions([
                'label' => $label,
                'data' => $this->fileResolver->resolve($field->getValue(), FileType::FILE),
                'mapped' => false,
                'required' => $configuration->isRequired(),
            ]);

        if (null !== $locale) {
            $element->setFormTypeOption('row_attr', [
                'data-content-translation-locale' => $locale->getId(),
            ]);
        }

        return $element;
    }
}
