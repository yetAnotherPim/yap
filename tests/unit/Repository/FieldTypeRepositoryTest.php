<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Repository\FieldTypeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldTypeRepository::class)]
class FieldTypeRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new FieldTypeRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(FieldTypeRepository::class, $repository);
    }
}
