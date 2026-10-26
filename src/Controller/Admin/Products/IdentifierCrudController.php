<?php

declare(strict_types=1);

namespace App\Controller\Admin\Products;

use App\Entity\Product\Identifier\Identifier;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractCrudController<Identifier>
 */
#[IsGranted('ROLE_EDITOR')]
class IdentifierCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Identifier::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('value', new TranslatableMessage('general.labels.value', [], 'admin'))
                ->setEmptyData(''),
        ];
    }
}
