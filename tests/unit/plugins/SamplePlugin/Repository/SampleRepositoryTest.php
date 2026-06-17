<?php

declare(strict_types=1);

namespace App\Tests\unit\plugins\SamplePlugin\Entity;

use App\plugins\SamplePlugin\Repository\SampleRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SampleRepository::class)]
class SampleRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new SampleRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(SampleRepository::class, $repository);
    }
}
