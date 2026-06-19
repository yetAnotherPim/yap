<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Repository\LocaleRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LocaleRepository::class)]
class LocaleRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new LocaleRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(LocaleRepository::class, $repository);
    }
}
