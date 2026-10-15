<?php

declare(strict_types=1);

namespace App\Form\User;

use App\Entity\User\User;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\Length;

class PasswordFormConfigurator
{
    private const int PASSWORD_MIN_LENGTH = 8;

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function configureField(bool $isRequired): FieldInterface
    {
        return TextField::new('password', new TranslatableMessage('user.labels.password', [], 'admin'))
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => new TranslatableMessage('user.labels.password', [], 'admin'),
                    'constraints' => [new Length(min: self::PASSWORD_MIN_LENGTH)],
                ],
                'second_options' => ['label' => new TranslatableMessage('user.labels.repeat_password', [], 'admin')],
                'mapped' => false,
            ])
            ->setRequired($isRequired)
            ->onlyOnForms();
    }

    /**
     * @param FormBuilderInterface<User> $formBuilder
     *
     * @return FormBuilderInterface<User>
     */
    public function addPasswordEventListener(FormBuilderInterface $formBuilder): FormBuilderInterface
    {
        return $formBuilder->addEventListener(FormEvents::POST_SUBMIT, $this->hashPassword(), 100);
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
