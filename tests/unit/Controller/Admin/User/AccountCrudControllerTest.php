<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\User;

use App\Controller\Admin\User\AccountCrudController;
use App\Entity\User\User;
use App\Form\User\PasswordFormConfigurator;
use App\Repository\ProductColumnConfigurationRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Uid\UuidV7;
use Symfony\Contracts\Translation\TranslatorInterface;

#[CoversClass(AccountCrudController::class)]
final class AccountCrudControllerTest extends MockeryTestCase
{
    private PasswordFormConfigurator&MockInterface $passwordFormConfigurator;

    private UserPasswordHasherInterface&MockInterface $passwordHasher;

    private TranslatorInterface&MockInterface $translator;
    private ProductColumnConfigurationRepository&MockInterface $productColumnConfigurationRepository;

    private AccountCrudController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passwordFormConfigurator = \Mockery::mock(PasswordFormConfigurator::class);
        $this->passwordHasher = \Mockery::mock(UserPasswordHasherInterface::class);
        $this->translator = \Mockery::mock(TranslatorInterface::class);
        $this->productColumnConfigurationRepository = \Mockery::mock(ProductColumnConfigurationRepository::class);

        $this->controller = new AccountCrudController(
            $this->passwordFormConfigurator,
            $this->passwordHasher,
            $this->translator,
            $this->productColumnConfigurationRepository,
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
