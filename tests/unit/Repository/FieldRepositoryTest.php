<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Repository\FieldRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldRepository::class)]
class FieldRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new FieldRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(FieldRepository::class, $repository);
    }
}
