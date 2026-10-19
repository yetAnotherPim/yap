<?php

declare(strict_types=1);

namespace App\Tests\unit\Repository;

use App\Repository\ProductTypeFieldConfigurationDefaultTranslationRepository;
use Doctrine\Persistence\ManagerRegistry;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProductTypeFieldConfigurationDefaultTranslationRepository::class)]
class ProductTypeFieldConfigurationDefaultTranslationRepositoryTest extends MockeryTestCase
{
    public function testInstantiation(): void
    {
        $registryMock = \Mockery::mock(ManagerRegistry::class);

        $repository = new ProductTypeFieldConfigurationDefaultTranslationRepository($registryMock);

        /* @phpstan-ignore-next-line */
        $this->assertInstanceOf(ProductTypeFieldConfigurationDefaultTranslationRepository::class, $repository);
    }
}
