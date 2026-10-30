<?php

declare(strict_types=1);

namespace App\Tests\unit\Setup\Command;

use App\Entity\User\Enum\Roles;
use App\Entity\User\User;
use App\Setup\Command\CreateUserCommand;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[CoversClass(CreateUserCommand::class)]
#[UsesClass(User::class)]
#[UsesClass(Roles::class)]
class CreateUserCommandTest extends MockeryTestCase
{
    public function testExecuteCreatesUserSuccessfully(): void
    {
        $persistedUser = null;

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager
            ->shouldReceive('persist')
            ->once()
            ->with(\Mockery::type(User::class))
            ->andReturnUsing(function (User $user) use (&$persistedUser): void {
                $persistedUser = $user;
            });

        $entityManager
            ->shouldReceive('flush')
            ->once();

        $passwordHasher = \Mockery::mock(UserPasswordHasherInterface::class);
        $passwordHasher
            ->shouldReceive('hashPassword')
            ->once()
            ->with(\Mockery::type(User::class), 'secret')
            ->andReturn('hashed-password');

        $validator = \Mockery::mock(ValidatorInterface::class);
        $validator
            ->shouldReceive('validate')
            ->once()
            ->with(\Mockery::type(User::class))
            ->andReturn(new ConstraintViolationList());

        $command = new CreateUserCommand(
            $entityManager,
            $passwordHasher,
            $validator,
        );

        $tester = new CommandTester($command);
        $tester->setInputs([
            'testuser',
            'test@example.com',
            'John',
            'Doe',
            Roles::ROLE_SUPER_ADMIN->name,
            'secret',
            'secret',
        ]);

        $exitCode = $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertInstanceOf(User::class, $persistedUser);

        $this->assertSame('testuser', $persistedUser->getUsername());
        $this->assertSame('test@example.com', $persistedUser->getEmail());
        $this->assertSame('John', $persistedUser->getFirstName());
        $this->assertSame('Doe', $persistedUser->getLastName());
        $this->assertSame(Roles::ROLE_SUPER_ADMIN, $persistedUser->getRole());
        $this->assertSame('hashed-password', $persistedUser->getPassword());

        $this->assertStringContainsString(
            'successfully created',
            $tester->getDisplay(),
        );
    }

    public function testExecuteFailsWhenPasswordIsEmpty(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldNotReceive('persist');
        $entityManager->shouldNotReceive('flush');

        $passwordHasher = \Mockery::mock(UserPasswordHasherInterface::class);
        $passwordHasher->shouldNotReceive('hashPassword');

        $validator = \Mockery::mock(ValidatorInterface::class);
        $validator->shouldNotReceive('validate');

        $command = new CreateUserCommand(
            $entityManager,
            $passwordHasher,
            $validator,
        );

        $tester = new CommandTester($command);
        $tester->setInputs([
            'testuser',
            'test@example.com',
            'John',
            'Doe',
            Roles::ROLE_SUPER_ADMIN->name,
            '',
            '',
        ]);

        $exitCode = $tester->execute([]);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString(
            'Password must not be empty.',
            $tester->getDisplay(),
        );
    }

    public function testExecuteFailsWhenPasswordsDoNotMatch(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldNotReceive('persist');
        $entityManager->shouldNotReceive('flush');

        $passwordHasher = \Mockery::mock(UserPasswordHasherInterface::class);
        $passwordHasher->shouldNotReceive('hashPassword');

        $validator = \Mockery::mock(ValidatorInterface::class);
        $validator->shouldNotReceive('validate');

        $command = new CreateUserCommand(
            $entityManager,
            $passwordHasher,
            $validator,
        );

        $tester = new CommandTester($command);
        $tester->setInputs([
            'testuser',
            'test@example.com',
            'John',
            'Doe',
            Roles::ROLE_SUPER_ADMIN->name,
            'secret',
            'different-secret',
        ]);

        $exitCode = $tester->execute([]);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString(
            'Passwords do not match.',
            $tester->getDisplay(),
        );
    }

    public function testExecuteFailsWhenUserIsInvalid(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldNotReceive('persist');
        $entityManager->shouldNotReceive('flush');

        $passwordHasher = \Mockery::mock(UserPasswordHasherInterface::class);
        $passwordHasher
            ->shouldReceive('hashPassword')
            ->once()
            ->with(\Mockery::type(User::class), 'secret')
            ->andReturn('hashed-password');

        $validator = \Mockery::mock(ValidatorInterface::class);
        $validator
            ->shouldReceive('validate')
            ->once()
            ->with(\Mockery::type(User::class))
            ->andReturn(new ConstraintViolationList([
                new ConstraintViolation(
                    'This value is already used.',
                    null,
                    [],
                    '',
                    'email',
                    'test@example.com',
                ),
            ]));

        $command = new CreateUserCommand(
            $entityManager,
            $passwordHasher,
            $validator,
        );

        $tester = new CommandTester($command);
        $tester->setInputs([
            'testuser',
            'test@example.com',
            'John',
            'Doe',
            Roles::ROLE_SUPER_ADMIN->name,
            'secret',
            'secret',
        ]);

        $exitCode = $tester->execute([]);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString(
            'This value is already used.',
            $tester->getDisplay(),
        );
    }
}
