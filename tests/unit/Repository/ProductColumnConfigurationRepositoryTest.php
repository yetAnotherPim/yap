<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Entity\User\Configuration\ProductColumnConfiguration;
use App\Entity\User\User;
use App\Repository\ProductColumnConfigurationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(ProductColumnConfigurationRepository::class)]
class ProductColumnConfigurationRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new ProductColumnConfigurationRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(ProductColumnConfigurationRepository::class, $repository);
    }

    public function testGetAvailableLocales(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $user = \Mockery::mock(User::class);
        $productColumnConfiguration1 = \Mockery::mock(ProductColumnConfiguration::class);
        $productColumnConfiguration2 = \Mockery::mock(ProductColumnConfiguration::class);

        $repository = \Mockery::mock(ProductColumnConfigurationRepository::class, [$registryMock])
            ->makePartial();

        $repository->shouldReceive('findBy')
            ->once()
            ->with(['user' => $user], ['position' => 'ASC'])
            ->andReturn(new ArrayCollection([UuidV7::v7()->toString() => $productColumnConfiguration1, UuidV7::v7()->toString() => $productColumnConfiguration2]));

        $result = $repository->getColumnsForUser($user);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame($productColumnConfiguration1, $result->first());
        $this->assertSame($productColumnConfiguration2, $result->last());
    }
}
