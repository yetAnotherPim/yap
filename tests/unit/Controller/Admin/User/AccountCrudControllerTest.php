<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\User;

use App\Controller\Admin\User\AccountCrudController;
use App\Entity\User\User;
use App\Form\User\PasswordFormConfigurator;
use App\Repository\ProductColumnConfigurationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\OptimisticLockException;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FormFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Uid\UuidV7;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(AccountCrudController::class)]
final class AccountCrudControllerTest extends MockeryTestCase
{
    private PasswordFormConfigurator&MockInterface $passwordFormConfigurator;

    private UserPasswordHasherInterface&MockInterface $passwordHasher;

    private TranslatorInterface&MockInterface $translator;
    private ProductColumnConfigurationRepository&MockInterface $productColumnConfigurationRepository;

    private CsrfTokenManagerInterface&MockInterface $csrfTokenManager;

    private AccountCrudController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passwordFormConfigurator = \Mockery::mock(PasswordFormConfigurator::class);
        $this->passwordHasher = \Mockery::mock(UserPasswordHasherInterface::class);
        $this->translator = \Mockery::mock(TranslatorInterface::class);
        $this->productColumnConfigurationRepository = \Mockery::mock(ProductColumnConfigurationRepository::class);
        $this->csrfTokenManager = \Mockery::mock(CsrfTokenManagerInterface::class);

        $this->controller = new AccountCrudController(
            $this->passwordFormConfigurator,
            $this->passwordHasher,
            $this->translator,
            $this->productColumnConfigurationRepository,
            $this->csrfTokenManager,
        );
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertSame(
            User::class,
            AccountCrudController::getEntityFqcn(),
        );
    }

    public function testConfigureFields(): void
    {
        $passwordField = TextField::new('password');

        $this->passwordFormConfigurator
            ->shouldReceive('configureField')
            ->once()
            ->with(true)
            ->andReturn($passwordField);

        $fields = iterator_to_array(
            $this->controller->configureFields('edit'),
        );

        $this->assertCount(2, $fields);

        $this->assertInstanceOf(TextField::class, $fields[0]);
        $this->assertSame('currentPassword', $fields[0]->getAsDto()->getProperty());

        $this->assertSame(
            $passwordField,
            $fields[1],
        );
    }

    public function testConfigureActionsDisablesExpectedActions(): void
    {
        $actions = Actions::new();

        $result = $this->controller->configureActions($actions);

        $disabledActions = $result
            ->getAsDto(Crud::PAGE_EDIT)
            ->getDisabledActions();

        $this->assertSame(
            [
                Action::INDEX,
                Action::NEW,
                Action::DELETE,
                Action::BATCH_DELETE,
                Action::DETAIL,
                Action::SAVE_AND_CONTINUE,
            ],
            $disabledActions,
        );
    }

    public function testIsGrantedReturnsFalseWhenSubjectIsNotCurrentUser(): void
    {
        $currentUser = \Mockery::mock(User::class);
        $subject = \Mockery::mock(User::class);

        $currentUser->shouldReceive('getId')
            ->once()
            ->andReturn(UuidV7::v7());

        $subject->shouldReceive('getId')
            ->once()
            ->andReturn(UuidV7::v7());

        $controller = \Mockery::mock(
            AccountCrudController::class,
            [
                $this->passwordFormConfigurator,
                $this->passwordHasher,
                $this->translator,
                $this->productColumnConfigurationRepository,
                $this->csrfTokenManager,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('getUser')
            ->once()
            ->andReturn($currentUser);

        $this->assertFalse(
            /* @phpstan-ignore-next-line */
            $controller->isGranted(Action::EDIT, $subject),
        );
    }

    public function testIsGrantedReturnsFalseWhenCurrentUserIsNotAUser(): void
    {
        $subject = \Mockery::mock(User::class);

        $controller = \Mockery::mock(
            AccountCrudController::class,
            [
                $this->passwordFormConfigurator,
                $this->passwordHasher,
                $this->translator,
                $this->productColumnConfigurationRepository,
                $this->csrfTokenManager,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('getUser')
            ->once()
            ->andReturn(null);

        $this->assertFalse(
            /* @phpstan-ignore-next-line */
            $controller->isGranted(Action::EDIT, $subject),
        );
    }

    public function testIsGrantedReturnsTrueForCurrentUserWhenParentGrantsAccess(): void
    {
        $currentUser = \Mockery::mock(User::class);
        $subject = \Mockery::mock(User::class);

        $userId = UuidV7::v7();

        $currentUser
            ->shouldReceive('getId')
            ->once()
            ->andReturn($userId);

        $subject
            ->shouldReceive('getId')
            ->once()
            ->andReturn($userId);

        $controller = \Mockery::mock(
            AccountCrudController::class,
            [
                $this->passwordFormConfigurator,
                $this->passwordHasher,
                $this->translator,
                $this->productColumnConfigurationRepository,
                $this->csrfTokenManager,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('getUser')
            ->once()
            ->andReturn($currentUser);

        $containerMock = \Mockery::mock(ContainerInterface::class);
        $securityCheckerMock = \Mockery::mock(AuthorizationCheckerInterface::class);

        $controller->setContainer($containerMock);

        $containerMock
            ->shouldReceive('has')
            ->with('security.authorization_checker')
            ->andReturn(true);

        $containerMock
            ->shouldReceive('get')
            ->with('security.authorization_checker')
            ->andReturn($securityCheckerMock);

        $securityCheckerMock
            ->shouldReceive('isGranted')
            ->once()
            ->with(Action::EDIT, $subject)
            ->andReturn(true);

        $this->assertTrue(
            /* @phpstan-ignore-next-line */
            $controller->isGranted(Action::EDIT, $subject),
        );
    }

    public function testIsGrantedDelegatesToParentWhenSubjectIsNotUser(): void
    {
        $controller = \Mockery::mock(
            AccountCrudController::class,
            [
                $this->passwordFormConfigurator,
                $this->passwordHasher,
                $this->translator,
                $this->productColumnConfigurationRepository,
                $this->csrfTokenManager,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->expectException(\Throwable::class);

        /* @phpstan-ignore-next-line */
        $controller->isGranted(Action::EDIT, new \stdClass());
    }

    public function testGetRedirectResponseAfterSaveRedirectsToAdmin(): void
    {
        $controller = new TestableAccountCrudController(
            $this->passwordFormConfigurator,
            $this->passwordHasher,
            $this->translator,
            $this->productColumnConfigurationRepository,
            $this->csrfTokenManager,
        );

        $container = \Mockery::mock(ContainerInterface::class);

        $controller->setContainer($container);

        $router = \Mockery::mock(\Symfony\Component\Routing\RouterInterface::class);

        $container
            ->shouldReceive('has')
            ->with('router')
            ->andReturn(true);

        $container
            ->shouldReceive('get')
            ->with('router')
            ->andReturn($router);

        $router
            ->shouldReceive('generate')
            ->once()
            ->with('admin', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_PATH)
            ->andReturn('/admin');

        /**
         * @var AdminContext<User> $context
         */
        $context = AdminContext::forTesting();

        $response = $controller->getRedirectResponseAfterSaveForTest(
            $context,
            Action::EDIT,
        );

        $this->assertSame('/admin', $response->getTargetUrl());
    }

    public function testCreateEditFormBuilderAddsPasswordEventListener(): void
    {
        /** @phpstan-var EntityDto<User> $entityDto */
        $entityDto = new EntityDto(
            User::class,
            \Mockery::mock(ClassMetadata::class),
        );

        $formOptions = KeyValueStore::new();

        /** @phpstan-var AdminContext<User> $context */
        $context = AdminContext::forTesting();

        /** @var FormBuilderInterface<User>&MockInterface $builder */
        $builder = \Mockery::mock(FormBuilderInterface::class);

        $builder
            ->shouldReceive('addEventListener')
            ->andReturnSelf();

        $formFactory = \Mockery::mock(\stdClass::class);

        $formFactory
            ->shouldReceive('createEditFormBuilder')
            ->once()
            ->with(
                $entityDto,
                $formOptions,
                $context,
            )
            ->andReturn($builder);

        $container = \Mockery::mock(ContainerInterface::class);

        $container
            ->shouldReceive('get')
            ->once()
            ->with(FormFactory::class)
            ->andReturn($formFactory);

        $controller = \Mockery::mock(
            AccountCrudController::class,
            [
                $this->passwordFormConfigurator,
                $this->passwordHasher,
                $this->translator,
                $this->productColumnConfigurationRepository,
                $this->csrfTokenManager,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->setContainer($container);

        $this->passwordFormConfigurator
            ->shouldReceive('addPasswordEventListener')
            ->once()
            ->with($builder);

        $result = $controller->createEditFormBuilder(
            $entityDto,
            $formOptions,
            $context,
        );

        $this->assertSame(
            $builder,
            $result,
        );
    }

    public function testCreateEditFormBuilderListenerReturnsForInvalidForm(): void
    {
        $listener = $this->getCreateEditFormBuilderListener();

        $form = \Mockery::mock(FormInterface::class);

        $form
            ->shouldReceive('isValid')
            ->once()
            ->andReturnFalse();

        $form->shouldNotReceive('getData');
        $form->shouldNotReceive('get');

        $listener(
            new PostSubmitEvent($form, null),
        );
    }

    public function testCreateEditFormBuilderListenerDoesNotAddErrorForValidCurrentPassword(): void
    {
        $listener = $this->getCreateEditFormBuilderListener();

        $user = \Mockery::mock(User::class);
        $currentPasswordField = \Mockery::mock(FormInterface::class);
        $form = \Mockery::mock(FormInterface::class);

        $form
            ->shouldReceive('isValid')
            ->once()
            ->andReturnTrue();

        $form
            ->shouldReceive('getData')
            ->once()
            ->andReturn($user);

        $form
            ->shouldReceive('get')
            ->once()
            ->with('currentPassword')
            ->andReturn($currentPasswordField);

        $currentPasswordField
            ->shouldReceive('getData')
            ->once()
            ->andReturn('correct-password');

        $this->passwordHasher
            ->shouldReceive('isPasswordValid')
            ->once()
            ->with($user, 'correct-password')
            ->andReturnTrue();

        $currentPasswordField->shouldNotReceive('addError');

        $listener(
            new PostSubmitEvent($form, null),
        );
    }

    public function testCreateEditFormBuilderListenerAddsErrorWhenCurrentPasswordIsNotString(): void
    {
        $listener = $this->getCreateEditFormBuilderListener();

        $user = \Mockery::mock(User::class);
        $currentPasswordField = \Mockery::mock(FormInterface::class);
        $form = \Mockery::mock(FormInterface::class);

        $form
            ->shouldReceive('isValid')
            ->once()
            ->andReturnTrue();

        $form
            ->shouldReceive('getData')
            ->once()
            ->andReturn($user);

        $form
            ->shouldReceive('get')
            ->with('currentPassword')
            ->andReturn($currentPasswordField);

        $currentPasswordField
            ->shouldReceive('getData')
            ->once()
            ->andReturn(null);

        $this->passwordHasher
            ->shouldNotReceive('isPasswordValid');

        $this->translator
            ->shouldReceive('trans')
            ->once()
            ->with(
                'account.messages.invalid_current_password',
                [],
                'admin',
            )
            ->andReturn('Das aktuelle Passwort ist ungültig.');

        $currentPasswordField
            ->shouldReceive('addError')
            ->once()
            ->with(
                \Mockery::on(
                    static fn (mixed $error): bool => $error instanceof FormError
                        && 'Das aktuelle Passwort ist ungültig.' === $error->getMessage(),
                ),
            );

        $listener(
            new PostSubmitEvent($form, null),
        );
    }

    /**
     * @return \Closure(PostSubmitEvent): void
     */
    private function getCreateEditFormBuilderListener(): \Closure
    {
        /** @var EntityDto<User> $entityDto */
        $entityDto = new EntityDto(
            User::class,
            \Mockery::mock(ClassMetadata::class),
        );

        $formOptions = KeyValueStore::new();

        /** @var AdminContext<User> $context */
        $context = AdminContext::forTesting();

        /** @var FormBuilderInterface<User>&MockInterface $builder */
        $builder = \Mockery::mock(FormBuilderInterface::class);

        /** @var \Closure|null $listener */
        $listener = null;

        $builder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::POST_SUBMIT,
                \Mockery::on(
                    static function (mixed $callback) use (&$listener): bool {
                        if (!$callback instanceof \Closure) {
                            return false;
                        }

                        $listener = $callback;

                        return true;
                    },
                ),
                100,
            )
            ->andReturnSelf();

        $formFactory = \Mockery::mock(\stdClass::class);

        $formFactory
            ->shouldReceive('createEditFormBuilder')
            ->once()
            ->with(
                $entityDto,
                $formOptions,
                $context,
            )
            ->andReturn($builder);

        $container = \Mockery::mock(ContainerInterface::class);

        $container
            ->shouldReceive('get')
            ->once()
            ->with(FormFactory::class)
            ->andReturn($formFactory);

        $controller = \Mockery::mock(
            AccountCrudController::class,
            [
                $this->passwordFormConfigurator,
                $this->passwordHasher,
                $this->translator,
                $this->productColumnConfigurationRepository,
                $this->csrfTokenManager,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->setContainer($container);

        $this->passwordFormConfigurator
            ->shouldReceive('addPasswordEventListener')
            ->once()
            ->with($builder);

        $controller->createEditFormBuilder(
            $entityDto,
            $formOptions,
            $context,
        );

        if (!$listener instanceof \Closure) {
            $this->fail('POST_SUBMIT listener was not registered.');
        }

        return $listener;
    }

    public function testConfigureColumns(): void
    {
        $user = \Mockery::mock(User::class);
        $configurations = new ArrayCollection();
        $unconfiguredFieldTypes = new ArrayCollection();

        $this->productColumnConfigurationRepository
            ->shouldReceive('getColumnsForUser')
            ->once()
            ->with($user)
            ->andReturn($configurations);

        $this->productColumnConfigurationRepository
            ->shouldReceive('getNotConfiguredFieldTypes')
            ->once()
            ->with($configurations)
            ->andReturn($unconfiguredFieldTypes);

        $this->translator
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('de');

        $controller = \Mockery::mock(
            AccountCrudController::class,
            [
                $this->passwordFormConfigurator,
                $this->passwordHasher,
                $this->translator,
                $this->productColumnConfigurationRepository,
                $this->csrfTokenManager,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('getUser')
            ->once()
            ->andReturn($user);

        $response = new Response('rendered content');

        $controller
            ->shouldReceive('render')
            ->once()
            ->with(
                'easyadmin/page/product_column_configuration/product_column_configuration.html.twig',
                [
                    'configurations' => $configurations,
                    'unconfiguredFieldTypes' => $unconfiguredFieldTypes,
                    'locale' => 'de',
                ],
            )
            ->andReturn($response);

        $result = $controller->configureColumns();

        $this->assertSame($response, $result);
    }

    public function testSaveColumnsReturnsBadRequestForInvalidCsrfToken(): void
    {
        $request = new Request(
            server: [
                'HTTP_CSRF-TOKEN' => 'invalid-token',
            ],
        );

        $this->csrfTokenManager
            ->shouldReceive('isTokenValid')
            ->once()
            ->with(
                \Mockery::on(
                    static fn (CsrfToken $token): bool => 'product_column_configuration' === $token->getId()
                        && 'invalid-token' === $token->getValue(),
                ),
            )
            ->andReturnFalse();

        $this->translator
            ->shouldReceive('trans')
            ->once()
            ->with(
                'product_column_configuration.errors.save_error',
                [],
                'admin',
            )
            ->andReturn('An error occurred while saving your column configuration.');

        $controller = \Mockery::mock(
            AccountCrudController::class,
            [
                $this->passwordFormConfigurator,
                $this->passwordHasher,
                $this->translator,
                $this->productColumnConfigurationRepository,
                $this->csrfTokenManager,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('addFlash')
            ->once()
            ->with(
                'danger',
                'An error occurred while saving your column configuration.',
            );

        $this->productColumnConfigurationRepository
            ->shouldNotReceive('synchronizeColumns');

        $response = $controller->saveColumns($request);

        $this->assertSame(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode(),
        );
    }

    public function testSaveColumnsReturnsNoContentAfterSuccessfulSave(): void
    {
        $user = \Mockery::mock(User::class);

        $request = Request::create(
            '/admin/product-column-configuration',
            'POST',
            server: [
                'HTTP_CSRF-TOKEN' => 'valid-token',
            ],
            content: json_encode([
                'fieldTypeIds' => [
                    'field-type-1',
                    'field-type-2',
                ],
            ], JSON_THROW_ON_ERROR),
        );

        $this->csrfTokenManager
            ->shouldReceive('isTokenValid')
            ->once()
            ->with(
                \Mockery::on(
                    static fn (CsrfToken $token): bool => 'product_column_configuration' === $token->getId()
                        && 'valid-token' === $token->getValue(),
                ),
            )
            ->andReturnTrue();

        $this->productColumnConfigurationRepository
            ->shouldReceive('synchronizeColumns')
            ->once()
            ->with(
                $user,
                [
                    'field-type-1',
                    'field-type-2',
                ],
            );

        $controller = \Mockery::mock(
            AccountCrudController::class,
            [
                $this->passwordFormConfigurator,
                $this->passwordHasher,
                $this->translator,
                $this->productColumnConfigurationRepository,
                $this->csrfTokenManager,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('getUser')
            ->once()
            ->andReturn($user);

        $response = $controller->saveColumns($request);

        $this->assertSame(
            Response::HTTP_NO_CONTENT,
            $response->getStatusCode(),
        );
    }

    public function testSaveColumnsReturnsBadRequestWhenSavingFails(): void
    {
        $user = \Mockery::mock(User::class);

        $request = Request::create(
            '/admin/product-column-configuration',
            'POST',
            server: [
                'HTTP_CSRF-TOKEN' => 'valid-token',
            ],
            content: json_encode([
                'fieldTypeIds' => ['field-type-1'],
            ], JSON_THROW_ON_ERROR),
        );

        $this->csrfTokenManager
            ->shouldReceive('isTokenValid')
            ->once()
            ->with(
                \Mockery::on(
                    static fn (CsrfToken $token): bool => 'product_column_configuration' === $token->getId()
                        && 'valid-token' === $token->getValue(),
                ),
            )
            ->andReturnTrue();

        $this->productColumnConfigurationRepository
            ->shouldReceive('synchronizeColumns')
            ->once()
            ->with($user, ['field-type-1'])
            ->andThrow(new OptimisticLockException('error', null));

        $this->translator
            ->shouldReceive('trans')
            ->once()
            ->with(
                'product_column_configuration.errors.save_error',
                [],
                'admin',
            )
            ->andReturn(
                'An error occurred while saving your column configuration.',
            );

        $controller = \Mockery::mock(
            AccountCrudController::class,
            [
                $this->passwordFormConfigurator,
                $this->passwordHasher,
                $this->translator,
                $this->productColumnConfigurationRepository,
                $this->csrfTokenManager,
            ],
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('getUser')
            ->once()
            ->andReturn($user);

        $controller
            ->shouldReceive('addFlash')
            ->once()
            ->with(
                'danger',
                'An error occurred while saving your column configuration.',
            );

        $response = $controller->saveColumns($request);

        $this->assertSame(
            Response::HTTP_BAD_REQUEST,
            $response->getStatusCode(),
        );
    }
}

final class TestableAccountCrudController extends AccountCrudController
{
    /**
     * @param AdminContext<User> $context
     */
    public function getRedirectResponseAfterSaveForTest(
        AdminContext $context,
        string $action,
    ): RedirectResponse {
        return $this->getRedirectResponseAfterSave($context, $action);
    }
}
