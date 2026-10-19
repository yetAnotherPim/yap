<?php

declare(strict_types=1);

namespace App\Form\Field\Section;

use App\Form\Field\Section\Type\SectionType;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Contracts\Translation\TranslatableInterface;

class SectionField implements FieldInterface
{
    use FieldTrait;

    public static function new(string $propertyName, TranslatableInterface|bool|string|null $label = null): self
    {
        return new self()
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setCssClass('field-section')
            ->setFormType(SectionType::class);
    }
}
