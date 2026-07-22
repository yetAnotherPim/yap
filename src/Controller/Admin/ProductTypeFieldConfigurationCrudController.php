<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

/**
 * @extends AbstractCrudController<ProductTypeFieldConfiguration>
 */
class ProductTypeFieldConfigurationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductTypeFieldConfiguration::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('fieldType')
                ->autocomplete(
                    callback: static fn (FieldType $fieldType): string => $fieldType->getTitle(),
                ),
            // TODO: hidden field
            IntegerField::new('position'),
        ];
    }
}
