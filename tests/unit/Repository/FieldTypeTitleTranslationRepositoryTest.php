<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Repository\FieldTypeTitleTranslationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FieldTypeTitleTranslationRepository::class)]
class FieldTypeTitleTranslationRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new FieldTypeTitleTranslationRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(FieldTypeTitleTranslationRepository::class, $repository);
    }
}
