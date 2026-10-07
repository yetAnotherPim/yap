<?php

declare(strict_types=1);

namespace App\Controller\Admin\User;

use App\Entity\User\User;
use App\Form\User\PasswordFormConfigurator;
use App\Repository\ProductColumnConfigurationRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Query\QueryException;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
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
        private readonly ProductColumnConfigurationRepository $productColumnConfigurationRepository,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    /**
     * @throws QueryException
     */
    #[Route(
        '/admin/product-column-configuration',
        name: 'admin_product_columns',
        methods: ['GET'],
    )]
    public function configureColumns(): Response
    {
        /** @phpstan-var User $user */
        $user = $this->getUser();

        $configurations = $this->productColumnConfigurationRepository
            ->getColumnsForUser($user);

        $unconfiguredFieldTypes = $this->productColumnConfigurationRepository
            ->getNotConfiguredFieldTypes($configurations);

        return $this->render('easyadmin/page/product/column_configuration/product_column_configuration.html.twig', [
            'configurations' => $configurations,
            'unconfiguredFieldTypes' => $unconfiguredFieldTypes,
            'locale' => $this->translator->getLocale(),
        ]);
    }

    #[Route(
        '/admin/product-column-configuration',
        name: 'admin_product_columns_save',
        methods: ['POST'],
    )]
    public function saveColumns(Request $request): Response
    {
        $csrfToken = $request->headers->get('csrf-token');

        if (!$this->csrfTokenManager->isTokenValid(
            new CsrfToken('product_column_configuration', $csrfToken),
        )) {
            $this->addFlash(
                'danger',
                $this->translator->trans(
                    'product_column_configuration.errors.save_error',
                    [],
                    'admin',
                ),
            );

            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        /** @phpstan-var User $user */
        $user = $this->getUser();

        /** @var array{fieldTypeIds?: list<string>} $data */
        $data = $request->toArray();

        try {
            $this->productColumnConfigurationRepository->synchronizeColumns(
                $user,
                $data['fieldTypeIds'] ?? [],
            );
        } catch (ORMException $exception) {
            $this->addFlash(
                'danger',
                $this->translator->trans(
                    'product_column_configuration.errors.save_error',
                    [],
                    'admin',
                ),
            );

            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        return new Response(status: Response::HTTP_NO_CONTENT);
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
