<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\Identifier;

use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Form\Product\Identifier\IdentifierManager;
use App\Repository\IdentifierTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Uid\Uuid;

#[CoversClass(IdentifierManager::class)]
#[UsesClass(Identifier::class)]
class IdentifierManagerTest extends MockeryTestCase
{
    /** @var IdentifierTypeRepository&MockInterface */
    private IdentifierTypeRepository $identifierTypeRepository;

    private IdentifierManager $identifierManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->identifierTypeRepository = \Mockery::mock(
            IdentifierTypeRepository::class,
        );

        $this->identifierManager = new IdentifierManager(
            $this->identifierTypeRepository,
        );
    }

    public function testInitializeIdentifiersAddsIdentifiersForAllIdentifierTypes(): void
    {
        $identifierType1 = \Mockery::mock(IdentifierType::class);
        $identifierType1
            ->shouldReceive('getId')
            ->andReturn(Uuid::v7());

        $identifierType2 = \Mockery::mock(IdentifierType::class);
        $identifierType2
            ->shouldReceive('getId')
            ->andReturn(Uuid::v7());

        $this->identifierTypeRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn([
                $identifierType1,
                $identifierType2,
            ]);

        $product = \Mockery::mock(Product::class);

        $product
            ->shouldReceive('getIdentifiers')
            ->twice()
            ->andReturn(new ArrayCollection());

        $product
            ->shouldReceive('addIdentifier')
            ->once()
            ->with(\Mockery::on(
                static fn (Identifier $identifier): bool => $identifier->getType() === $identifierType1,
            ));

        $product
            ->shouldReceive('addIdentifier')
            ->once()
            ->with(\Mockery::on(
                static fn (Identifier $identifier): bool => $identifier->getType() === $identifierType2,
            ));

        $this->identifierManager->initializeIdentifiers($product);
    }

    public function testInitializeIdentifiersDoesNothingWhenNoIdentifierTypesExist(): void
    {
        $this->identifierTypeRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn([]);

        $product = \Mockery::mock(Product::class);

        $product
            ->shouldNotReceive('getIdentifiers');

        $product
            ->shouldNotReceive('addIdentifier');

        $this->identifierManager->initializeIdentifiers($product);
    }

    public function testInitializeIdentifiersAddsOnlyMissingIdentifiers(): void
    {
        $existingTypeId = Uuid::v7();
        $missingTypeId = Uuid::v7();

        $existingIdentifierType = \Mockery::mock(IdentifierType::class);
        $existingIdentifierType
            ->shouldReceive('getId')
            ->andReturn($existingTypeId);

        $missingIdentifierType = \Mockery::mock(IdentifierType::class);
        $missingIdentifierType
            ->shouldReceive('getId')
            ->once()
            ->andReturn($missingTypeId);

        $this->identifierTypeRepository
            ->shouldReceive('findAll')
            ->once()
            ->andReturn([
                $existingIdentifierType,
                $missingIdentifierType,
            ]);

        $identifier = \Mockery::mock(Identifier::class);
        $identifier
            ->shouldReceive('getType')
            ->times(2)
            ->andReturn($existingIdentifierType);

        $product = \Mockery::mock(Product::class);

        $product
            ->shouldReceive('getIdentifiers')
            ->times(2)
            ->andReturn(new ArrayCollection([$identifier]));

        $product
            ->shouldReceive('addIdentifier')
            ->once()
            ->with(\Mockery::on(
                static fn (Identifier $identifier): bool => $identifier->getType() === $missingIdentifierType,
            ));

        $this->identifierManager->initializeIdentifiers($product);
    }

    public function testClearUnsetIdentifiersRemovesEmptyIdentifiers(): void
    {
        $emptyIdentifier = \Mockery::mock(Identifier::class);
        $emptyIdentifier
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('');

        $validIdentifier = \Mockery::mock(Identifier::class);
        $validIdentifier
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('ABC123');

        $product = \Mockery::mock(Product::class);

        $product
            ->shouldReceive('getIdentifiers')
            ->once()
            ->andReturn(
                new ArrayCollection([
                    $emptyIdentifier,
                    $validIdentifier,
                ]),
            );

        $product
            ->shouldReceive('removeIdentifier')
            ->once()
            ->with($emptyIdentifier);

        $product
            ->shouldReceive('removeIdentifier')
            ->never()
            ->with($validIdentifier);

        $this->identifierManager->clearUnsetIdentifiers($product);
    }

    public function testClearUnsetIdentifiersDoesNothingWhenAllIdentifiersHaveValues(): void
    {
        $identifier1 = \Mockery::mock(Identifier::class);
        $identifier1
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('ABC123');

        $identifier2 = \Mockery::mock(Identifier::class);
        $identifier2
            ->shouldReceive('getValue')
            ->once()
            ->andReturn('XYZ456');

        $product = \Mockery::mock(Product::class);

        $product
            ->shouldReceive('getIdentifiers')
            ->once()
            ->andReturn(
                new ArrayCollection([
                    $identifier1,
                    $identifier2,
                ]),
            );

        $product
            ->shouldNotReceive('removeIdentifier');

        $this->identifierManager->clearUnsetIdentifiers($product);
    }
}
