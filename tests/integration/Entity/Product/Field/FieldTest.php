<?php

declare(strict_types=1);

namespace App\Tests\integration\Entity\Product\Field;

use App\Doctrine\Event\EventListener\RelatedTimestampUpdater;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use App\Kernel;
use App\PluginManager\DependencyInjection\CompilerPass\PluginLoader;
use App\PluginManager\PluginManager;
use App\Repository\FieldRepository;
use App\Repository\IdentifierRepository;
use App\Tests\TestData\Factory\Product\Field\FieldFactory;
use App\Tests\TestData\Factory\Product\Type\ProductTypeFactory;
use App\Tests\TestData\Story\ProductsWithFieldsStory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

#[CoversClass(Product::class)]
#[CoversClass(RelatedTimestampUpdater::class)]
#[CoversClass(Field::class)]
#[UsesClass(Identifier::class)]
#[UsesClass(IdentifierType::class)]
#[UsesClass(IdentifierRepository::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(FieldRepository::class)]
#[UsesClass(Kernel::class)]
#[UsesClass(PluginLoader::class)]
#[UsesClass(PluginManager::class)]
#[UsesClass(ProductType::class)]
class FieldTest extends KernelTestCase
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
        ProductsWithFieldsStory::load();

        $repository = $this->entityManager->getRepository(Field::class);
        $fields = new ArrayCollection($repository->findAll());

        $this->assertCount(4, $fields);

        $field = $fields->first();
        $this->assertInstanceOf(Field::class, $field);

        $product = $field->getProduct();

        $this->entityManager->remove($product);
        $this->entityManager->flush();

        $fields = new ArrayCollection($repository->findAll());
        $this->assertCount(2, $fields);
    }

    public function testSettingUpdatedAtOnUpdate(): void
    {
        $productType = ProductTypeFactory::createOne();

        $product = new Product();
        $product->setProductType($productType);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $this->assertNull($product->getUpdatedAt());

        $field = FieldFactory::createOne();

        $product->addField($field);

        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $this->assertNotNull($product->getUpdatedAt());
    }
}
