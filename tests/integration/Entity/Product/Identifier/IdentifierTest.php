<?php

declare(strict_types=1);

namespace App\Tests\integration\Entity\Product\Identifier;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Field\Type\Section\FieldSection;
use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Repository\FieldRepository;
use App\Repository\IdentifierRepository;
use App\Tests\TestData\Factory\Product\Identifier\IdentifierFactory;
use App\Tests\TestData\Story\ProductsSharingIdentifierStory;
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
#[UsesClass(Field::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(FieldSection::class)]
#[UsesClass(FieldRepository::class)]
class IdentifierTest extends KernelTestCase
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

    public function testOrphanRemoval(): void
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
        $product = new Product();

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $this->assertNull($product->getUpdatedAt());

        $identifier = IdentifierFactory::createOne();

        $product->addIdentifier($identifier);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $this->assertNotNull($product->getUpdatedAt());
    }
}
