<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Repository\IdentifierTypeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(IdentifierTypeRepository::class)]
class IdentifierTypeRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new IdentifierTypeRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(IdentifierTypeRepository::class, $repository);
    }
}
