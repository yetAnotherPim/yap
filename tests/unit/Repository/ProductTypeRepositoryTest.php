<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Repository\ProductTypeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProductTypeRepository::class)]
class ProductTypeRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new ProductTypeRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(ProductTypeRepository::class, $repository);
    }
}
