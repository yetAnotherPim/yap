<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class FieldTypeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FieldType::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('code'),
            TextField::new('title'),
            BooleanField::new('translatable'),
            BooleanField::new('filterable'),
            ChoiceField::new('inputType')
                ->setChoices(InputType::cases()),
        ];
    }
}
