<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Repository\FieldSectionRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldSectionRepository::class)]
class FieldSectionRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new FieldSectionRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(FieldSectionRepository::class, $repository);
    }
}
