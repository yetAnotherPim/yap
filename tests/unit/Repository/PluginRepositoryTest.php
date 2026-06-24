<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Repository\PluginRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PluginRepository::class)]
class PluginRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new PluginRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(PluginRepository::class, $repository);
    }
}
