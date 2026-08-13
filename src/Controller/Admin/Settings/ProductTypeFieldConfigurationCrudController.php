<?php

declare(strict_types=1);

namespace App\Controller\Admin\Settings;

use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @extends AbstractCrudController<ProductTypeFieldConfiguration>
 */
#[IsGranted('ROLE_ADMIN')]
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
                ->setHtmlAttribute('data-title-field', 'true')
                ->setRequired(true)
                ->autocomplete(
                    callback: static fn (FieldType $fieldType): string => $fieldType->getTitle(),
                ),
            TextareaField::new('defaultValue'),
            BooleanField::new('required'),
            HiddenField::new('position'),
        ];
    }
}
