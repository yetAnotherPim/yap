<?php

declare(strict_types=1);

namespace App\Tests\unit\DemoData\Provider;

use App\DemoData\Provider\IdentifierTypeProvider;
use App\Entity\Product\Identifier\Type\Enum\DataType;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Repository\IdentifierTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(IdentifierTypeProvider::class)]
#[UsesClass(IdentifierType::class)]
class IdentifierTypeProviderTest extends MockeryTestCase
{
    public function testGetIdentifierTypesCreatesMissingTypes(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $repository = \Mockery::mock(IdentifierTypeRepository::class);

        foreach (['SKU', 'GTIN'] as $title) {
            $repository
                ->shouldReceive('findOneBy')
                ->once()
                ->with(['title' => $title])
                ->andReturnNull();
        }

        $entityManager
            ->shouldReceive('persist')
            ->twice()
            ->with(\Mockery::type(IdentifierType::class));

        $provider = new IdentifierTypeProvider(
            $entityManager,
            $repository,
        );

        $result = $provider->getIdentifierTypes();

        $this->assertSame(['sku', 'gtin'], array_keys($result));

        $this->assertSame('SKU', $result['sku']->getTitle());
        $this->assertSame('GTIN', $result['gtin']->getTitle());

        $this->assertSame(DataType::String, $result['sku']->getDataType());
        $this->assertSame(DataType::String, $result['gtin']->getDataType());
    }

    public function testGetIdentifierTypesReusesExistingTypes(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $repository = \Mockery::mock(IdentifierTypeRepository::class);

        $sku = \Mockery::mock(IdentifierType::class);
        $gtin = \Mockery::mock(IdentifierType::class);

        $repository
            ->shouldReceive('findOneBy')
            ->once()
            ->with(['title' => 'SKU'])
            ->andReturn($sku);

        $repository
            ->shouldReceive('findOneBy')
            ->once()
            ->with(['title' => 'GTIN'])
            ->andReturn($gtin);

        $entityManager
            ->shouldNotReceive('persist');

        $provider = new IdentifierTypeProvider(
            $entityManager,
            $repository,
        );

        $result = $provider->getIdentifierTypes();

        $this->assertSame($sku, $result['sku']);
        $this->assertSame($gtin, $result['gtin']);
    }
}
