<?php

declare(strict_types=1);

namespace App\Controller\Admin\Settings;

use App\Entity\User\Enum\Roles;
use App\Entity\User\User;
use App\Form\User\PasswordFormConfigurator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractCrudController<User>
 */
#[IsGranted('ROLE_SUPER_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public function __construct(private readonly PasswordFormConfigurator $passwordFormConfigurator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(new TranslatableMessage('user.entity.title', [], 'admin'))
            ->setEntityLabelInPlural(new TranslatableMessage('user.entity.title_plural', [], 'admin'));
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            FormField::addTab(new TranslatableMessage('user.labels.master_data', [], 'admin')),
            TextField::new('username', new TranslatableMessage('user.labels.username', [], 'admin'))
                ->setRequired(true),
            ChoiceField::new('role', new TranslatableMessage('user.labels.role', [], 'admin'))
                ->setChoices(Roles::cases()),
            EmailField::new('email', new TranslatableMessage('user.labels.email', [], 'admin'))
                ->setRequired(true),
            TextField::new('firstName', new TranslatableMessage('user.labels.first_name', [], 'admin'))
                ->setRequired(true),
            TextField::new('lastName', new TranslatableMessage('user.labels.last_name', [], 'admin'))
                ->setRequired(true),
            FormField::addTab('Password'),
            $this->passwordFormConfigurator->configureField(Crud::PAGE_NEW === $pageName),
        ];

        return $fields;
    }

    /**
     * @return FormBuilderInterface<User>
     */
    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);

        return $this->passwordFormConfigurator->addPasswordEventListener($formBuilder);
    }

    /**
     * @return FormBuilderInterface<User>
     */
    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);

        return $this->passwordFormConfigurator->addPasswordEventListener($formBuilder);
    }
}
