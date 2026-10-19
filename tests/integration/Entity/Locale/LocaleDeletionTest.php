<?php

declare(strict_types=1);

namespace App\Tests\integration\Entity\Locale;

use App\Doctrine\Event\EventListener\RelatedTimestampUpdater;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Field\Type\Translation\FieldTypeTitleTranslation;
use App\Entity\Product\Product;
use App\Entity\Product\Type\FieldConfiguration\DefaultTranslation\ProductTypeFieldConfigurationDefaultTranslation;
use App\Entity\Product\Type\FieldConfiguration\ProductTypeFieldConfiguration;
use App\Entity\Product\Type\ProductType;
use App\Entity\Product\Type\Translation\ProductTypeTitleTranslation;
use App\Repository\LocaleRepository;
use App\Tests\TestData\Factory\Locale\LocaleFactory;
use App\Tests\TestData\Factory\Product\Field\FieldFactory;
use App\Tests\TestData\Factory\Product\Field\Translation\FieldValueTranslationFactory;
use App\Tests\TestData\Factory\Product\Field\Type\FieldTypeFactory;
use App\Tests\TestData\Factory\Product\Field\Type\Translation\FieldTypeTitleTranslationFactory;
use App\Tests\TestData\Factory\Product\Type\FieldConfiguration\DefaultTranslation\ProductTypeFieldConfigurationDefaultTranslationFactory;
use App\Tests\TestData\Factory\Product\Type\FieldConfiguration\ProductTypeFieldConfigurationFactory;
use App\Tests\TestData\Factory\Product\Type\ProductTypeFactory;
use App\Tests\TestData\Factory\Product\Type\Translation\ProductTypeTitleTranslationFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

#[CoversClass(Locale::class)]
#[UsesClass(Field::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(Product::class)]
#[UsesClass(ProductType::class)]
#[UsesClass(ProductTypeFieldConfiguration::class)]
#[UsesClass(ProductTypeFieldConfigurationDefaultTranslation::class)]
#[UsesClass(FieldTypeTitleTranslation::class)]
#[UsesClass(FieldValueTranslation::class)]
#[UsesClass(ProductTypeTitleTranslation::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(RelatedTimestampUpdater::class)]
class LocaleDeletionTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function testDeletingLocaleDeletesAssociatedTranslations(): void
    {
        $de = LocaleFactory::createOne([
            'id' => 'de_DE',
        ]);

        $es = LocaleFactory::createOne([
            'id' => 'es_ES',
        ]);

        $productType = ProductTypeFactory::createOne();

        $fieldType = FieldTypeFactory::createOne();

        $configuration = ProductTypeFieldConfigurationFactory::createOne([
            'productType' => $productType,
            'fieldType' => $fieldType,
        ]);

        $field = FieldFactory::createOne([
            'type' => $fieldType,
        ]);

        $deProductTypeTitleTranslation =
            ProductTypeTitleTranslationFactory::createOne([
                'locale' => $de,
                'productType' => $productType,
                'value' => 'Deutsch',
            ]);

        $esProductTypeTitleTranslation =
            ProductTypeTitleTranslationFactory::createOne([
                'locale' => $es,
                'productType' => $productType,
                'value' => 'Español',
            ]);

        $deFieldTypeTitleTranslation =
            FieldTypeTitleTranslationFactory::createOne([
                'locale' => $de,
                'fieldType' => $fieldType,
                'value' => 'Deutsch',
            ]);

        $esFieldTypeTitleTranslation =
            FieldTypeTitleTranslationFactory::createOne([
                'locale' => $es,
                'fieldType' => $fieldType,
                'value' => 'Español',
            ]);

        $deFieldValueTranslation =
            FieldValueTranslationFactory::createOne([
                'locale' => $de,
                'field' => $field,
                'value' => 'Deutsch',
            ]);

        $esFieldValueTranslation =
            FieldValueTranslationFactory::createOne([
                'locale' => $es,
                'field' => $field,
                'value' => 'Español',
            ]);

        $deDefaultTranslation =
            ProductTypeFieldConfigurationDefaultTranslationFactory::createOne([
                'locale' => $de,
                'productTypeFieldConfiguration' => $configuration,
                'value' => 'Deutsch',
            ]);

        $esDefaultTranslation =
            ProductTypeFieldConfigurationDefaultTranslationFactory::createOne([
                'locale' => $es,
                'productTypeFieldConfiguration' => $configuration,
                'value' => 'Español',
            ]);

        $deProductTypeTitleTranslationId =
            $deProductTypeTitleTranslation->getId();
        $esProductTypeTitleTranslationId =
            $esProductTypeTitleTranslation->getId();

        $deFieldTypeTitleTranslationId =
            $deFieldTypeTitleTranslation->getId();
        $esFieldTypeTitleTranslationId =
            $esFieldTypeTitleTranslation->getId();

        $deFieldValueTranslationId =
            $deFieldValueTranslation->getId();
        $esFieldValueTranslationId =
            $esFieldValueTranslation->getId();

        $deDefaultTranslationId =
            $deDefaultTranslation->getId();
        $esDefaultTranslationId =
            $esDefaultTranslation->getId();

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(
            EntityManagerInterface::class,
        );

        $entityManager->remove($de);
        $entityManager->flush();
        $entityManager->clear();

        $this->assertNull(
            $entityManager->find(
                Locale::class,
                'de_DE',
            ),
        );

        $this->assertNull(
            $entityManager->find(
                ProductTypeTitleTranslation::class,
                $deProductTypeTitleTranslationId,
            ),
        );

        $this->assertNull(
            $entityManager->find(
                FieldTypeTitleTranslation::class,
                $deFieldTypeTitleTranslationId,
            ),
        );

        $this->assertNull(
            $entityManager->find(
                FieldValueTranslation::class,
                $deFieldValueTranslationId,
            ),
        );

        $this->assertNull(
            $entityManager->find(
                ProductTypeFieldConfigurationDefaultTranslation::class,
                $deDefaultTranslationId,
            ),
        );

        // Translations of other locales must remain.
        $this->assertNotNull(
            $entityManager->find(
                ProductTypeTitleTranslation::class,
                $esProductTypeTitleTranslationId,
            ),
        );

        $this->assertNotNull(
            $entityManager->find(
                FieldTypeTitleTranslation::class,
                $esFieldTypeTitleTranslationId,
            ),
        );

        $this->assertNotNull(
            $entityManager->find(
                FieldValueTranslation::class,
                $esFieldValueTranslationId,
            ),
        );

        $this->assertNotNull(
            $entityManager->find(
                ProductTypeFieldConfigurationDefaultTranslation::class,
                $esDefaultTranslationId,
            ),
        );
    }
}
