<?php

declare(strict_types=1);

namespace App\Controller\Admin\Products;

use App\Entity\Product\Identifier\Identifier;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Identifier>
 */
class IdentifierCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Identifier::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('value')
                ->setEmptyData(''),
        ];
    }
}
