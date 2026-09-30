<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Settings;

use App\Controller\Admin\Settings\UserCrudController;
use App\Entity\User\User;
use App\Form\User\PasswordFormConfigurator;
use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FormFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Container\ContainerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Translation\TranslatableMessage;

#[CoversClass(UserCrudController::class)]
class UserCrudControllerTest extends MockeryTestCase
{
    private UserCrudController $controller;

    /** @phpstan-var PasswordFormConfigurator|MockInterface  */
    private PasswordFormConfigurator $passwordFormConfigurator;

    public function setUp(): void
    {
        $this->passwordFormConfigurator = \Mockery::mock(PasswordFormConfigurator::class);

        $this->controller = new UserCrudController($this->passwordFormConfigurator);

        parent::setUp();
    }

    public function testConfigureFieldsForReturnsExpectedFields(): void
    {
        $passwordField = TextField::new('password');

        $this->passwordFormConfigurator
            ->shouldReceive('configureField')
            ->once()
            ->with(false)
            ->andReturn($passwordField);

        $fields = iterator_to_array($this->controller->configureFields(Crud::PAGE_INDEX));
        $this->assertCount(8, $fields);

        $this->assertInstanceOf(FormField::class, $fields[0]);
        $this->assertInstanceOf(TextField::class, $fields[1]);
        $this->assertEquals('username', $fields[1]->getAsDto()->getProperty());
        $this->assertInstanceOf(ChoiceField::class, $fields[2]);
        $this->assertEquals('role', $fields[2]->getAsDto()->getProperty());
        $this->assertInstanceOf(EmailField::class, $fields[3]);
        $this->assertEquals('email', $fields[3]->getAsDto()->getProperty());
        $this->assertInstanceOf(TextField::class, $fields[4]);
        $this->assertEquals('firstName', $fields[4]->getAsDto()->getProperty());
        $this->assertInstanceOf(TextField::class, $fields[5]);
        $this->assertEquals('lastName', $fields[5]->getAsDto()->getProperty());
        $this->assertInstanceOf(FormField::class, $fields[6]);
        $this->assertInstanceOf(TextField::class, $fields[7]);
        $this->assertEquals('password', $fields[7]->getAsDto()->getProperty());
    }

    public function testGetEntityFqcn(): void
    {
        $this->assertEquals(User::class, UserCrudController::getEntityFqcn());
    }

    public function testCreateNewFormBuilderAddsPasswordEventListener(): void
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

        $formFactory = \Mockery::mock(\stdClass::class);

        $formFactory
            ->shouldReceive('createNewFormBuilder')
            ->once()
            ->with($entityDto, $formOptions, $context)
            ->andReturn($builder);

        $container = \Mockery::mock(ContainerInterface::class);

        $container
            ->shouldReceive('get')
            ->once()
            ->with(FormFactory::class)
            ->andReturn($formFactory);

        $this->controller->setContainer($container);

        $this->passwordFormConfigurator
            ->shouldReceive('addPasswordEventListener')
            ->once()
            ->with($builder)
            ->andReturn($builder);

        $result = $this->controller->createNewFormBuilder(
            $entityDto,
            $formOptions,
            $context,
        );

        $this->assertSame($builder, $result);
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

        $formFactory = \Mockery::mock(\stdClass::class);

        $formFactory
            ->shouldReceive('createEditFormBuilder')
            ->once()
            ->with($entityDto, $formOptions, $context)
            ->andReturn($builder);

        $container = \Mockery::mock(ContainerInterface::class);

        $container
            ->shouldReceive('get')
            ->once()
            ->with(FormFactory::class)
            ->andReturn($formFactory);

        $this->controller->setContainer($container);

        $this->passwordFormConfigurator
            ->shouldReceive('addPasswordEventListener')
            ->once()
            ->with($builder)
            ->andReturn($builder);

        $result = $this->controller->createEditFormBuilder(
            $entityDto,
            $formOptions,
            $context,
        );

        $this->assertSame($builder, $result);
    }

    public function testConfigureCrud(): void
    {
        $crud = Crud::new();

        $configuredCrud = $this->controller->configureCrud($crud);

        $this->assertInstanceOf(TranslatableMessage::class, $configuredCrud->getAsDto()->getEntityLabelInPlural());
        $this->assertEquals(
            'user.entity.title_plural',
            $configuredCrud->getAsDto()->getEntityLabelInPlural()->getMessage()
        );

        $this->assertInstanceOf(TranslatableMessage::class, $configuredCrud->getAsDto()->getEntityLabelInSingular());
        $this->assertEquals(
            'user.entity.title',
            $configuredCrud->getAsDto()->getEntityLabelInSingular()->getMessage()
        );
    }
}
