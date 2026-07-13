<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Product\Identifier\Type\Enum\DataType;
use App\Entity\Product\Identifier\Type\IdentifierType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<IdentifierType>
 */
class IdentifierTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return IdentifierType::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            ChoiceField::new('dataType')
                ->setChoices(DataType::cases()),
        ];
    }
}
