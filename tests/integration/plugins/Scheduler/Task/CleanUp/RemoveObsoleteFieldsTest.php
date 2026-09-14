<?php

declare(strict_types=1);

namespace App\Tests\integration\plugins\Scheduler\Task\CleanUp;

use App\Doctrine\Event\EventListener\RelatedTimestampUpdater;
use App\Entity\Abstract\RelatedTimestampableEntity;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Entity\Product\Type\ProductType;
use App\plugins\Scheduler\Task\CleanUp\RemoveObsoleteFields;
use App\Repository\FieldRepository;
use App\Tests\TestData\Factory\Product\Field\FieldFactory;
use App\Tests\TestData\Factory\Product\Field\Type\FieldTypeFactory;
use App\Tests\TestData\Factory\Product\ProductFactory;
use App\Tests\TestData\Factory\Product\Type\FieldConfiguration\ProductTypeFieldConfigurationFactory;
use App\Tests\TestData\Factory\Product\Type\ProductTypeFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RemoveObsoleteFields::class)]
#[UsesClass(RelatedTimestampableEntity::class)]
#[UsesClass(Field::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(Product::class)]
#[UsesClass(ProductType::class)]
#[UsesClass(ProductTypeFieldConfiguration::class)]
#[UsesClass(FieldRepository::class)]
#[UsesClass(RelatedTimestampUpdater::class)]
class RemoveObsoleteFieldsTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $this->assertInstanceOf(
            EntityManagerInterface::class,
            $entityManager,
        );

        $this->entityManager = $entityManager;
    }

    public function testRemoveObsoleteFieldsRemovesOnlyFieldsNotConfiguredForProductType(): void
    {
        $allowedFieldType = FieldTypeFactory::createOne([
            'code' => 'description',
            'title' => 'Description',
            'inputType' => InputType::Text,
            'translatable' => true,
        ]);

        $obsoleteFieldType = FieldTypeFactory::createOne([
            'code' => 'material',
            'title' => 'Material',
            'inputType' => InputType::Text,
            'translatable' => true,
        ]);

        $productType = ProductTypeFactory::createOne([
            'code' => 'smartphone',
            'title' => 'Smartphone',
        ]);

        ProductTypeFieldConfigurationFactory::createOne([
            'productType' => $productType,
            'fieldType' => $allowedFieldType,
        ]);

        $product = ProductFactory::createOne([
            'productType' => $productType,
        ]);

        $allowedField = FieldFactory::createOne([
            'product' => $product,
            'type' => $allowedFieldType,
            'value' => 'A smartphone',
        ]);

        $obsoleteField = FieldFactory::createOne([
            'product' => $product,
            'type' => $obsoleteFieldType,
            'value' => 'Aluminium',
        ]);

        $allowedFieldId = $allowedField->getId();
        $obsoleteFieldId = $obsoleteField->getId();

        $task = new RemoveObsoleteFields(
            $this->entityManager,
        );

        $task->run();

        $this->entityManager->clear();

        $fieldRepository = $this->entityManager->getRepository(Field::class);

        $this->assertNotNull(
            $fieldRepository->find($allowedFieldId),
        );

        $this->assertNull(
            $fieldRepository->find($obsoleteFieldId),
        );
    }
}
