<?php

declare(strict_types=1);

namespace App\Form\Product\Fields\Renderer\Registry;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('form.product.fields.renderer')]
interface FieldRendererInterface
{
    public function supports(FieldType $type): bool;

    public function render(
        Field $field, ProductTypeFieldConfiguration $configuration,
    ): FieldInterface;
}
