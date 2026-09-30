<?php

declare(strict_types=1);

namespace App\Controller\Admin\User;

use App\Entity\User\User;
use App\Form\User\PasswordFormConfigurator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractCrudController<User>
 */
#[IsGranted('ROLE_EDITOR')]
class AccountCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly PasswordFormConfigurator $passwordFormConfigurator,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly TranslatorInterface $translator,
    ) {
    }

    protected function getRedirectResponseAfterSave(
        AdminContext $context,
        string $action,
    ): RedirectResponse {
        return $this->redirectToRoute('admin');
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            TextField::new(
                'currentPassword',
                new TranslatableMessage('account.labels.current_password', [], 'admin'),
            )
                ->setFormType(PasswordType::class)
                ->setFormTypeOptions([
                    'mapped' => false,
                ])
                ->onlyOnForms(),
            $this->passwordFormConfigurator->configureField(true),
        ];

        return $fields;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(
            Action::INDEX,
            Action::NEW,
            Action::DELETE,
            Action::BATCH_DELETE,
            Action::DETAIL,
            Action::SAVE_AND_CONTINUE
        );
    }

    /**
     * @return FormBuilderInterface<User>
     */
    public function createEditFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $formOptions,
        AdminContext $context,
    ): FormBuilderInterface {
        $formBuilder = parent::createEditFormBuilder(
            $entityDto,
            $formOptions,
            $context,
        );

        $formBuilder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (PostSubmitEvent $event): void {
                $form = $event->getForm();

                if (!$form->isValid()) {
                    return;
                }

                /** @var User $user */
                $user = $form->getData();

                $currentPassword = $form->get('currentPassword')->getData();

                if (
                    !\is_string($currentPassword)
                    || !$this->passwordHasher->isPasswordValid($user, $currentPassword)
                ) {
                    $form->get('currentPassword')->addError(
                        new FormError(
                            $this->translator->trans(
                                'account.messages.invalid_current_password',
                                [],
                                'admin',
                            ),
                        ),
                    );
                }
            },
            100,
        );

        $this->passwordFormConfigurator->addPasswordEventListener($formBuilder);

        return $formBuilder;
    }

    protected function isGranted(mixed $attribute, mixed $subject = null): bool
    {
        if ($subject instanceof User) {
            $currentUser = $this->getUser();

            if (
                !$currentUser instanceof User
                || $subject->getId() !== $currentUser->getId()
            ) {
                return false;
            }
        }

        return parent::isGranted($attribute, $subject);
    }
}
