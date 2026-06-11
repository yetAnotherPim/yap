<?php

declare(strict_types=1);

namespace App\Tests\integration\Entity\Product;

use App\Entity\Identifier\Identifier;
use App\Entity\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Repository\IdentifierRepository;
use App\Tests\Story\ProductsSharingIdentifierStory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

#[CoversClass(Product::class)]
#[UsesClass(Identifier::class)]
#[UsesClass(IdentifierType::class)]
#[UsesClass(IdentifierRepository::class)]
class ProductTest extends KernelTestCase
{
    use Factories;
    private EntityManagerInterface $entityManager;

    public function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        $this->entityManager = $entityManager;

        parent::setUp();
    }

    public function testOrphanRemovalOfIdentifiers(): void
    {
        ProductsSharingIdentifierStory::load();

        $repository = $this->entityManager->getRepository(Identifier::class);
        $identifiers = new ArrayCollection($repository->findAll());

        $this->assertCount(4, $identifiers);

        $identifier = $identifiers->first();
        $this->assertInstanceOf(Identifier::class, $identifier);

        $product = $identifier->getProduct();

        $this->entityManager->remove($product);
        $this->entityManager->flush();

        $identifiers = new ArrayCollection($repository->findAll());
        $this->assertCount(2, $identifiers);
    }

    public function testSettingUpdatedAtOnUpdate(): void
    {
        // TODO: should get updated on association change (addIdentifier etc.)
        $product = new Product();

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $this->assertNull($product->getUpdatedAt());

        $product->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $this->assertNotNull($product->getUpdatedAt());
    }
}
