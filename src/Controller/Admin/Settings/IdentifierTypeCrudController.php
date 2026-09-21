<?php

declare(strict_types=1);

namespace App\Controller\Admin\Settings;

use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Repository\IdentifierTypeRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractCrudController<IdentifierType>
 */
#[IsGranted('ROLE_ADMIN')]
class IdentifierTypeCrudController extends AbstractCrudController
{
    public function __construct(private readonly IdentifierTypeRepository $identifierTypeRepository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return IdentifierType::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(new TranslatableMessage('identifier_type.entity.title', [], 'admin'))
            ->setEntityLabelInPlural(new TranslatableMessage('identifier_type.entity.title_plural', [], 'admin'));
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', new TranslatableMessage('identifier_type.labels.title', [], 'admin')),
            ChoiceField::new('dataType', new TranslatableMessage('identifier_type.labels.data_type', [], 'admin')),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::DELETE,
                fn (Action $action) => $action->displayIf(fn (IdentifierType $identifierType) => !$this->identifierTypeRepository->isInUse($identifierType))
            )
            ->update(Crud::PAGE_DETAIL, Action::DELETE,
                fn (Action $action) => $action->displayIf(fn (IdentifierType $identifierType) => !$this->identifierTypeRepository->isInUse($identifierType))
            )
            ->disable(Action::BATCH_DELETE);
    }
}
