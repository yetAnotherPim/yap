<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\User;

use App\Entity\User\User;
use App\Form\User\PasswordFormConfigurator;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Length;

#[CoversClass(PasswordFormConfigurator::class)]
final class PasswordFormConfiguratorTest extends MockeryTestCase
{
    private UserPasswordHasherInterface&Mockery\MockInterface $passwordHasher;

    private PasswordFormConfigurator $configurator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passwordHasher = \Mockery::mock(
            UserPasswordHasherInterface::class,
        );

        $this->configurator = new PasswordFormConfigurator(
            $this->passwordHasher,
        );
    }

    public function testConfigureFieldCreatesPasswordField(): void
    {
        $field = $this->configurator->configureField(false);

        $this->assertInstanceOf(TextField::class, $field);

        $dto = $field->getAsDto();

        $this->assertSame(
            'password',
            $dto->getProperty(),
        );

        $this->assertInstanceOf(
            TranslatableMessage::class,
            $dto->getLabel(),
        );

        $this->assertSame(
            'user.labels.password',
            $dto->getLabel()->getMessage(),
        );

        $this->assertSame(
            'admin',
            $dto->getLabel()->getDomain(),
        );

        $this->assertSame(
            RepeatedType::class,
            $dto->getFormType(),
        );

        $options = $dto->getFormTypeOptions();

        $this->assertSame(
            PasswordType::class,
            $options['type'],
        );

        $this->assertFalse(
            $options['mapped'],
        );

        /**
         * @var array{
         *     label: TranslatableMessage,
         *     constraints: list<Length>
         * } $firstOptions
         */
        $firstOptions = $options['first_options'];

        /**
         * @var array{
         *     label: TranslatableMessage
         * } $secondOptions
         */
        $secondOptions = $options['second_options'];

        $this->assertSame(
            'user.labels.password',
            $firstOptions['label']->getMessage(),
        );

        $this->assertSame(
            'admin',
            $firstOptions['label']->getDomain(),
        );

        $this->assertSame(
            'user.labels.repeat_password',
            $secondOptions['label']->getMessage(),
        );

        $this->assertSame(
            'admin',
            $secondOptions['label']->getDomain(),
        );

        $this->assertCount(
            1,
            $firstOptions['constraints'],
        );

        $this->assertSame(
            8,
            $firstOptions['constraints'][0]->min,
        );
    }

    public function testConfigureFieldAcceptsRequiredParameter(): void
    {
        $optionalField = $this->configurator->configureField(false);
        $requiredField = $this->configurator->configureField(true);

        $this->assertInstanceOf(
            TextField::class,
            $optionalField,
        );

        $this->assertInstanceOf(
            TextField::class,
            $requiredField,
        );

        $this->assertSame(
            'password',
            $optionalField->getAsDto()->getProperty(),
        );

        $this->assertSame(
            'password',
            $requiredField->getAsDto()->getProperty(),
        );
    }

    public function testAddPasswordEventListenerRegistersPostSubmitListener(): void
    {
        /** @var FormBuilderInterface<User>&Mockery\MockInterface $formBuilder */
        $formBuilder = \Mockery::mock(
            FormBuilderInterface::class,
        );

        $formBuilder
            ->shouldReceive('addEventListener')
            ->once()
            ->with(
                FormEvents::POST_SUBMIT,
                \Mockery::type(\Closure::class),
            )
            ->andReturnSelf();

        $result = $this->configurator->addPasswordEventListener(
            $formBuilder,
        );

        $this->assertSame(
            $formBuilder,
            $result,
        );
    }
}
