<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Repository\ProductTypeFieldConfigurationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProductTypeFieldConfigurationRepository::class)]
class ProductTypeFieldConfigurationRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new ProductTypeFieldConfigurationRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(ProductTypeFieldConfigurationRepository::class, $repository);
    }
}
