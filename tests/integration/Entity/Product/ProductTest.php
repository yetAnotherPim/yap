<?php

declare(strict_types=1);

namespace App\Tests\integration\Entity\Product;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Field\Type\Section\FieldSection;
use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Repository\FieldRepository;
use App\Repository\IdentifierRepository;
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

    public function testSettingUpdatedAtOnUpdate(): void
    {
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
