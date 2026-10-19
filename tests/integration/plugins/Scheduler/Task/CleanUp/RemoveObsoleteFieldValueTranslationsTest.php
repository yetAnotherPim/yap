<?php

declare(strict_types=1);

namespace App\Tests\integration\plugins\Scheduler\Task\CleanUp;

use App\Doctrine\Event\EventListener\RelatedTimestampUpdater;
use App\Entity\Abstract\RelatedTimestampableEntity;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use App\PluginManager\PluginManager;
use App\plugins\Scheduler\Task\CleanUp\RemoveObsoleteFieldValueTranslations;
use App\Repository\FieldValueTranslationRepository;
use App\Repository\LocaleRepository;
use App\Tests\TestData\Factory\Locale\LocaleFactory;
use App\Tests\TestData\Factory\Product\Field\FieldFactory;
use App\Tests\TestData\Factory\Product\Field\Translation\FieldValueTranslationFactory;
use App\Tests\TestData\Factory\Product\Field\Type\FieldTypeFactory;
use App\Tests\TestData\Factory\Product\ProductFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[CoversClass(RemoveObsoleteFieldValueTranslations::class)]
#[UsesClass(RelatedTimestampableEntity::class)]
#[UsesClass(Field::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(FieldValueTranslation::class)]
#[UsesClass(Locale::class)]
#[UsesClass(Product::class)]
#[UsesClass(ProductType::class)]
#[UsesClass(FieldValueTranslationRepository::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(RelatedTimestampUpdater::class)]
#[UsesClass(PluginManager::class)]
class RemoveObsoleteFieldValueTranslationsTest extends KernelTestCase
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

    public function testRemoveObsoleteFieldValueTranslationsRemovesOnlyTranslationsOfNonTranslatableFields(): void
    {
        $locale = LocaleFactory::findOrCreate([
            'id' => 'de_DE',
        ]);

        $translatableFieldType = FieldTypeFactory::createOne([
            'code' => 'description',
            'title' => 'Description',
            'inputType' => InputType::Text,
            'translatable' => true,
        ]);

        $nonTranslatableFieldType = FieldTypeFactory::createOne([
            'code' => 'marketplace_ebay',
            'title' => 'eBay',
            'inputType' => InputType::CheckBox,
            'translatable' => false,
        ]);

        $product = ProductFactory::createOne();

        $translatableField = FieldFactory::createOne([
            'product' => $product,
            'type' => $translatableFieldType,
            'value' => 'Description',
        ]);

        $nonTranslatableField = FieldFactory::createOne([
            'product' => $product,
            'type' => $nonTranslatableFieldType,
            'value' => '1',
        ]);

        $translationToKeep = FieldValueTranslationFactory::createOne([
            'field' => $translatableField,
            'locale' => $locale,
            'value' => 'Beschreibung',
        ]);

        $translationToDelete = FieldValueTranslationFactory::createOne([
            'field' => $nonTranslatableField,
            'locale' => $locale,
            'value' => '1',
        ]);

        $translationToKeepId = $translationToKeep->getId();
        $translationToDeleteId = $translationToDelete->getId();

        $task = new RemoveObsoleteFieldValueTranslations(
            $this->entityManager,
        );

        $task->run();

        $this->entityManager->clear();

        $translationRepository = $this->entityManager
            ->getRepository(FieldValueTranslation::class);

        $this->assertNotNull(
            $translationRepository->find($translationToKeepId),
        );

        $this->assertNull(
            $translationRepository->find($translationToDeleteId),
        );
    }
}
