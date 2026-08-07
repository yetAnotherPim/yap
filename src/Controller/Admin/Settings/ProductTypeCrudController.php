<?php

declare(strict_types=1);

namespace App\Controller\Admin\Settings;

use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Entity\Product\Type\ProductType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @extends AbstractCrudController<ProductType>
 */
#[IsGranted('ROLE_ADMIN')]
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
                ->setHtmlAttribute('data-title-update-selector-prefix', 'ProductType_productTypeFieldConfigurations_')
                ->setRequired(true)
                ->setEntryIsComplex()
                ->setLabel(false)
                ->addCssClass('sortable-collection')
                ->setEntryToStringMethod(
                    fn (ProductTypeFieldConfiguration $productTypeFieldConfiguration): string => (null !== $productTypeFieldConfiguration->getFieldType())
                        ? $productTypeFieldConfiguration->getFieldType()->getTitle().' ('.$productTypeFieldConfiguration->getFieldType()->getCode().' - '.$productTypeFieldConfiguration->getFieldType()->getInputType()->name.')'
                        : ''
                ),
        ];
    }
}
