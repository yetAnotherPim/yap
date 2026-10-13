<?php

declare(strict_types=1);

namespace App\Tests\integration\Messenger\ProductUpdate;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Messenger\ProductUpdate\Message\Dto\FieldTranslation;
use App\Messenger\ProductUpdate\Message\Dto\FieldTypeIdentifier;
use App\Messenger\ProductUpdate\Message\Dto\ProductField;
use App\Messenger\ProductUpdate\Message\Enum\FieldTypeIdentifierType;
use App\Messenger\ProductUpdate\Message\ProductUpdateMessage;
use App\Messenger\ProductUpdate\ProductUpdateMessageHandler;
use App\Messenger\ProductUpdate\Resolver\FieldResolver;
use App\Messenger\ProductUpdate\Resolver\FieldValueTranslationResolver;
use App\Repository\ProductRepository;
use App\Tests\TestData\Factory\Locale\LocaleFactory;
use App\Tests\TestData\Factory\Product\Field\FieldFactory;
use App\Tests\TestData\Factory\Product\Field\Translation\FieldValueTranslationFactory;
use App\Tests\TestData\Factory\Product\Field\Type\FieldTypeFactory;
use App\Tests\TestData\Factory\Product\ProductFactory;
use App\Tests\TestData\Factory\Product\Type\FieldConfiguration\ProductTypeFieldConfigurationFactory;
use App\Tests\TestData\Factory\Product\Type\ProductTypeFactory;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

use function PHPUnit\Framework\assertInstanceOf;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Attribute\ResetDatabase;

#[CoversClass(ProductUpdateMessageHandler::class)]
#[UsesClass(FieldResolver::class)]
#[UsesClass(FieldValueTranslationResolver::class)]
#[UsesClass(Product::class)]
#[UsesClass(Field::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(FieldValueTranslation::class)]
#[ResetDatabase]
class ProductUpdateMessageHandlerTest extends KernelTestCase
{
    use MockeryPHPUnitIntegration;

    private EntityManagerInterface $entityManager;

    private ProductRepository $productRepository;

    private ProductUpdateMessageHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $container = static::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);
        assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $productRepository = $container->get(ProductRepository::class);
        assertInstanceOf(ProductRepository::class, $productRepository);

        $handler = $container->get(ProductUpdateMessageHandler::class);
        assertInstanceOf(ProductUpdateMessageHandler::class, $handler);

        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
        $this->handler = $handler;
    }

    public function testUpdatesFieldAndExistingTranslationAndAddsNewTranslation(): void
    {
        $germanLocale = LocaleFactory::findOrCreate([
            'id' => 'de_DE',
            'contentTranslationEnabled' => true,
        ]);

        $englishLocale = LocaleFactory::findOrCreate([
            'id' => 'en_GB',
            'contentTranslationEnabled' => false,
        ]);

        $spanishLocale = LocaleFactory::findOrCreate([
            'id' => 'es_ES',
            'contentTranslationEnabled' => true,
        ]);

        $productType = ProductTypeFactory::createOne();

        $fieldType = FieldTypeFactory::createOne([
            'code' => 'product_name',
            'translatable' => true,
        ]);

        ProductTypeFieldConfigurationFactory::createOne([
            'productType' => $productType,
            'fieldType' => $fieldType,
        ]);

        $product = ProductFactory::createOne([
            'productType' => $productType,
        ]);

        $field = FieldFactory::createOne([
            'product' => $product,
            'type' => $fieldType,
            'value' => 'Old name',
        ]);

        FieldValueTranslationFactory::createOne([
            'field' => $field,
            'locale' => $germanLocale,
            'value' => 'Alter Name',
        ]);

        $productId = (string) $product->getId();

        $productField = new ProductField(
            new FieldTypeIdentifier(
                FieldTypeIdentifierType::CODE,
                'product_name',
            ),
            'New name',
        );

        $productField->addTranslation(
            new FieldTranslation($germanLocale->getId(), 'Neuer Name'),
        );

        $productField->addTranslation(
            new FieldTranslation($englishLocale->getId(), 'English name'),
        );

        $productField->addTranslation(
            new FieldTranslation($spanishLocale->getId(), 'Nombre en español'),
        );

        $message = new ProductUpdateMessage($productId);
        $message->addField($productField);

        $this->handler->handleProductUpdateMessage($message);

        $this->entityManager->clear();

        $updatedProduct = $this->productRepository->find($productId);

        $this->assertInstanceOf(Product::class, $updatedProduct);

        $updatedField = $updatedProduct->getFields()->findFirst(
            static fn (int|string $key, Field $field): bool => 'product_name' === $field->getType()->getCode(),
        );

        $this->assertInstanceOf(Field::class, $updatedField);
        $this->assertSame('New name', $updatedField->getValue());

        // german translation has been updated
        $this->assertSame(
            'Neuer Name',
            $updatedField->getTranslationByLocale('de_DE')?->getValue(),
        );

        // english is not available for content translations
        $this->assertNull(
            $updatedField->getTranslationByLocale('en_GB'),
        );

        // added new spanish translation
        $this->assertSame(
            'Nombre en español',
            $updatedField->getTranslationByLocale('es_ES')?->getValue(),
        );

        $this->assertCount(2, $updatedField->getTranslations());
    }

    public function testCreatesConfiguredFieldAndSkipsUnconfiguredField(): void
    {
        $product = ProductFactory::createOne();

        $configuredFieldType = FieldTypeFactory::createOne([
            'code' => 'configured_field',
            'translatable' => false,
        ]);

        $unconfiguredFieldType = FieldTypeFactory::createOne([
            'code' => 'unconfigured_field',
            'translatable' => false,
        ]);

        $this->configureFieldType($product, $configuredFieldType);

        $message = $this->createMessage(
            (string) $product->getId(),
            new ProductField(
                new FieldTypeIdentifier(
                    FieldTypeIdentifierType::CODE,
                    'configured_field',
                ),
                'Created value',
            ),
            new ProductField(
                new FieldTypeIdentifier(
                    FieldTypeIdentifierType::CODE,
                    'unconfigured_field',
                ),
                'Must not be created',
            ),
        );

        $this->handler->handleProductUpdateMessage($message);

        $product = $this->reloadProduct((string) $product->getId());

        $this->assertCount(1, $product->getFields());

        $field = $this->findField($product, 'configured_field');

        $this->assertSame('Created value', $field->getValue());
        $this->assertNull($this->findFieldOrNull($product, 'unconfigured_field'));
    }

    public function testDoesNotCreateTranslationForNonTranslatableField(): void
    {
        $product = ProductFactory::createOne();

        $fieldType = FieldTypeFactory::createOne([
            'code' => 'non_translatable_field',
            'translatable' => false,
        ]);

        $this->configureFieldType($product, $fieldType);

        LocaleFactory::findOrCreate(['id' => 'de_DE']);

        $message = $this->createMessage(
            (string) $product->getId(),
            new ProductField(
                new FieldTypeIdentifier(
                    FieldTypeIdentifierType::CODE,
                    'non_translatable_field',
                ),
                'Value',
            ),
        );

        /* @phpstan-ignore-next-line */
        $message->getFields()->first()->addTranslation(
            new FieldTranslation('de_DE', 'Übersetzung'),
        );

        $this->handler->handleProductUpdateMessage($message);

        $product = $this->reloadProduct((string) $product->getId());
        $field = $this->findField($product, 'non_translatable_field');

        $this->assertSame('Value', $field->getValue());
        $this->assertCount(0, $field->getTranslations());
    }

    public function testSetsNullForFieldAndExistingTranslation(): void
    {
        $product = ProductFactory::createOne();

        $fieldType = FieldTypeFactory::createOne([
            'code' => 'nullable_field',
            'translatable' => true,
        ]);

        $this->configureFieldType($product, $fieldType);

        $locale = LocaleFactory::findOrCreate(['id' => 'de_DE']);

        $field = FieldFactory::createOne([
            'product' => $product,
            'type' => $fieldType,
            'value' => 'Old value',
        ]);

        FieldValueTranslationFactory::createOne([
            'field' => $field,
            'locale' => $locale,
            'value' => 'Alter Wert',
        ]);

        $message = $this->createMessage(
            (string) $product->getId(),
            new ProductField(
                new FieldTypeIdentifier(
                    FieldTypeIdentifierType::ID,
                    (string) $fieldType->getId(),
                ),
                null,
            ),
        );

        /* @phpstan-ignore-next-line */
        $message->getFields()->first()->addTranslation(
            new FieldTranslation('de_DE', null),
        );

        $this->handler->handleProductUpdateMessage($message);

        $product = $this->reloadProduct((string) $product->getId());
        $field = $this->findField($product, 'nullable_field');

        $this->assertNull($field->getValue());
        $this->assertNull($this->findTranslation($field, 'de_DE')->getValue());
    }

    public function testDatabaseExceptionIsPropagated(): void
    {
        $product = ProductFactory::createOne();

        $databaseException = new \PDOException('Simulated database failure');

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('persist')->once();
        $entityManager->shouldReceive('flush')
            ->once()
            ->andThrow($databaseException);

        $fieldResolver = static::getContainer()->get(FieldResolver::class);
        $this->assertInstanceOf(FieldResolver::class, $fieldResolver);

        $fieldValueTranslationResolver = static::getContainer()->get(FieldValueTranslationResolver::class);
        $this->assertInstanceOf(FieldValueTranslationResolver::class, $fieldValueTranslationResolver);

        $handler = new ProductUpdateMessageHandler(
            $this->productRepository,
            $fieldResolver,
            $fieldValueTranslationResolver,
            $entityManager,
        );

        $this->expectException(\PDOException::class);
        $this->expectExceptionMessage('Simulated database failure');

        $handler->handleProductUpdateMessage(
            new ProductUpdateMessage((string) $product->getId()),
        );
    }

    private function configureFieldType(
        Product $product,
        FieldType $fieldType,
    ): void {
        ProductTypeFieldConfigurationFactory::createOne([
            'productType' => $product->getProductType(),
            'fieldType' => $fieldType,
        ]);
    }

    private function createMessage(
        string $productId,
        ProductField ...$fields,
    ): ProductUpdateMessage {
        $message = new ProductUpdateMessage($productId);

        foreach ($fields as $field) {
            $message->addField($field);
        }

        return $message;
    }

    private function reloadProduct(string $productId): Product
    {
        $this->entityManager->clear();

        $product = $this->productRepository->find($productId);

        $this->assertInstanceOf(Product::class, $product);

        return $product;
    }

    private function findField(Product $product, string $code): Field
    {
        $field = $this->findFieldOrNull($product, $code);

        $this->assertInstanceOf(Field::class, $field);

        return $field;
    }

    private function findFieldOrNull(Product $product, string $code): ?Field
    {
        $field = $product->getFields()->findFirst(
            static fn (int $key, Field $field): bool => $field->getType()->getCode() === $code,
        );

        return $field instanceof Field ? $field : null;
    }

    private function findTranslation(
        Field $field,
        string $localeId,
    ): FieldValueTranslation {
        $translation = $field->getTranslations()->findFirst(
            static fn (int $key, FieldValueTranslation $translation): bool => (string) $translation->getLocale()->getId() === $localeId,
        );

        $this->assertInstanceOf(FieldValueTranslation::class, $translation);

        return $translation;
    }
}
