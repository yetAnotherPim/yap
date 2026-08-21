<?php

declare(strict_types=1);

namespace App\Controller\Admin\Settings;

use App\Entity\User\Enum\Roles;
use App\Entity\User\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @extends AbstractCrudController<User>
 */
#[IsGranted('ROLE_SUPER_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
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
        ];

        $password = TextField::new('password', new TranslatableMessage('user.labels.password', [], 'admin'))
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => ['label' => new TranslatableMessage('user.labels.password', [], 'admin')],
                'second_options' => ['label' => new TranslatableMessage('user.labels.repeat_password', [], 'admin')],
                'mapped' => false,
            ])
            ->setRequired(Crud::PAGE_NEW === $pageName)
            ->onlyOnForms()
        ;
        $fields[] = $password;

        return $fields;
    }

    /**
     * @return FormBuilderInterface<User>
     */
    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);

        return $this->addPasswordEventListener($formBuilder);
    }

    /**
     * @return FormBuilderInterface<User>
     */
    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);

        return $this->addPasswordEventListener($formBuilder);
    }

    /**
     * @param FormBuilderInterface<User> $formBuilder
     *
     * @return FormBuilderInterface<User>
     */
    private function addPasswordEventListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $this->hashPassword());
    }

    /**
     * @codeCoverageIgnore
     */
    private function hashPassword(): \Closure
    {
        return function (PostSubmitEvent $event): void {
            $form = $event->getForm();

            if (false === $form->isValid()) {
                return;
            }

            $password = $form->get('password')->getData();
            if (null === $password || false === \is_string($password)) {
                return;
            }

            $hash = $this->passwordHasher->hashPassword(new User(), $password);
            /** @phpstan-var User $user */
            $user = $form->getData();

            $user->setPassword($hash);
        };
    }
}
