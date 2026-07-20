<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Entity\Product\Type\ProductType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<ProductType>
 */
class ProductTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductType::class;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                TextField::new('code'),
                TextField::new('title'),
            ];
        }

        return [
            TextField::new('code'),
            TextField::new('title'),
            FormField::addFieldset('Fields'),
            CollectionField::new('productTypeFieldConfigurations')
                ->setEntryIsComplex()
                ->setLabel(false)
                ->setSortable(true)
                ->setEntryToStringMethod(
                    fn (ProductTypeFieldConfiguration $productTypeFieldConfiguration): string => $productTypeFieldConfiguration->getFieldType()?->getTitle() ?? ''
                ),
        ];
    }
}
