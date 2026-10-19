<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Entity\User\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[CoversClass(UserRepository::class)]
class UserRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new UserRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(UserRepository::class, $repository);
    }

    public function testUpgradePassword(): void
    {
        $user = \Mockery::mock(User::class);

        $user->shouldReceive('setPassword')
            ->once()
            ->with('hashed-password');

        $registryMock = \Mockery::mock(ManagerRegistry::class);
        $entityManagerMock = \Mockery::mock(EntityManagerInterface::class);
        $classMetadata = new ClassMetadata(User::class);

        $registryMock
            ->shouldReceive('getManagerForClass')
            ->once()
            ->andReturn($entityManagerMock);

        $entityManagerMock
            ->shouldReceive('getClassMetadata')
            ->once()
            ->andReturn($classMetadata);

        $entityManagerMock
            ->shouldReceive('persist')
            ->once()
            ->with($user);

        $entityManagerMock
            ->shouldReceive('flush')
            ->once();

        $repository = new UserRepository($registryMock);

        $repository->upgradePassword($user, 'hashed-password');
    }

    public function testUpgradePasswordWithInvalidUserInstance(): void
    {
        $user = \Mockery::mock(PasswordAuthenticatedUserInterface::class);

        $user
            ->shouldReceive('setPassword')
            ->never();

        $registryMock = \Mockery::mock(ManagerRegistry::class);
        $repository = new UserRepository($registryMock);

        $this->expectException(UnsupportedUserException::class);

        $repository->upgradePassword($user, 'hashed-password');
    }
}
