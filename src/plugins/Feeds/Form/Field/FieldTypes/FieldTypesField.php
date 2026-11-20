<?php

declare(strict_types=1);

namespace App\plugins\Feeds\Form\Field\FieldTypes;

use App\plugins\Feeds\Form\Field\FieldTypes\Type\FieldTypesType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;

final class FieldTypesField implements FieldInterface
{
    use FieldTrait;

    public static function new(
        string $propertyName,
        TranslatableInterface|string|bool|null $label = null,
    ): self {
        return new self()
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType(FieldTypesType::class)
            ->setFormTypeOption('mapped', false)
            ->setFormTypeOption('required', false)
            ->addFormTheme('@Feeds/admin/feeds/field_types.html.twig');
    }

    /**
     * @param list<\App\Entity\Product\Field\Type\FieldType> $fieldTypes
     */
    public function setFieldTypes(array $fieldTypes): self
    {
        $this->setFormTypeOption('field_types', $fieldTypes);

        return $this;
    }
}
