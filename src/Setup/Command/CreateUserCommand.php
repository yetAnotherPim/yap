<?php

declare(strict_types=1);

namespace App\Setup\Command;

use App\Entity\User\Enum\Roles;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'yap:user:create',
    description: 'Create a new YAP user account.',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp(<<<'HELP'
        Creates a new user account for Yet Another PIM (YAP).

        The command interactively asks for:
          - Username
          - Email address
          - First name
          - Last name
          - User role
          - Password and password confirmation

        Available roles are determined by the Roles enum.

        The password is securely hashed before being stored.
        HELP);
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $io = new SymfonyStyle($input, $output);

        $io->title('Yet Another PIM - Create User');

        /** @var string|null $username */
        $username = $io->ask('Username');

        /** @var string|null $email */
        $email = $io->ask('Email address');

        /** @var string|null $firstName */
        $firstName = $io->ask('First name');

        /** @var string|null $lastName */
        $lastName = $io->ask('Last name');

        /** @var string $role */
        $role = $io->choice(
            'Select user role',
            array_column(Roles::cases(), 'name'),
            Roles::ROLE_SUPER_ADMIN->name,
        );

        $user = new User()
            ->setUsername($username ?? '')
            ->setEmail($email ?? '')
            ->setFirstName($firstName ?? '')
            ->setLastName($lastName ?? '')
            ->setRole(Roles::fromName($role));

        /** @var string|null $password */
        $password = $io->askHidden('Password');
        $passwordConfirmation = $io->askHidden('Confirm password');

        if (null === $password || '' === $password) {
            $io->error('Password must not be empty.');

            return Command::FAILURE;
        }

        if ($password !== $passwordConfirmation) {
            $io->error('Passwords do not match.');

            return Command::FAILURE;
        }

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $password),
        );

        $violations = $this->validator->validate($user);

        if (\count($violations) > 0) {
            $io->error((string) $violations);

            return Command::FAILURE;
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(\sprintf(
            'User "%s" (%s) was successfully created with role "%s".',
            $user->getUsername(),
            $user->getEmail(),
            $user->getRole()->name,
        ));

        return Command::SUCCESS;
    }
}
