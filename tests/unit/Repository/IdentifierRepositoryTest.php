<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Repository\IdentifierRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IdentifierRepository::class)]
class IdentifierRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new IdentifierRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(IdentifierRepository::class, $repository);
    }
}
