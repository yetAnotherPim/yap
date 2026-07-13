<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Locale\Locale;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<Locale>
 */
class LocaleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Locale::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('id')
                ->setMaxLength(8),
        ];
    }
}
