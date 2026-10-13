<?php

declare(strict_types=1);

namespace App\Tests\unit\Messenger\ProductUpdate;

use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Product;
use App\Messenger\ProductUpdate\Message\Dto\FieldTranslation;
use App\Messenger\ProductUpdate\Message\Dto\FieldTypeIdentifier;
use App\Messenger\ProductUpdate\Message\Dto\ProductField;
use App\Messenger\ProductUpdate\Message\ProductUpdateMessage;
use App\Messenger\ProductUpdate\ProductUpdateMessageHandler;
use App\Messenger\ProductUpdate\Resolver\FieldResolver;
use App\Messenger\ProductUpdate\Resolver\FieldValueTranslationResolver;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProductUpdateMessageHandler::class)]
class ProductUpdateMessageHandlerTest extends MockeryTestCase
{
    public function testHandleProductUpdateMessageIgnoresNonExistingProduct(): void
    {
        $message = \Mockery::mock(ProductUpdateMessage::class);
        $message->shouldReceive('getProductId')
            ->once()
            ->andReturn('123');

        $productRepository = \Mockery::mock(ProductRepository::class);
        $productRepository->shouldReceive('find')
            ->once()
            ->with('123')
            ->andReturnNull();

        $fieldResolver = \Mockery::mock(FieldResolver::class);
        $fieldResolver->shouldNotReceive('resolve');

        $translationResolver = \Mockery::mock(FieldValueTranslationResolver::class);
        $translationResolver->shouldNotReceive('resolve');

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldNotReceive('persist');
        $entityManager->shouldNotReceive('flush');

        $handler = new ProductUpdateMessageHandler(
            $productRepository,
            $fieldResolver,
            $translationResolver,
            $entityManager,
        );

        $handler->handleProductUpdateMessage($message);
    }

    public function testHandleProductUpdateMessageUpdatesFieldAndTranslation(): void
    {
        $identifier = \Mockery::mock(FieldTypeIdentifier::class);

        $translationData = \Mockery::mock(FieldTranslation::class);
        $translationData->shouldReceive('getLocaleId')
            ->once()
            ->andReturn('de_DE');
        $translationData->shouldReceive('getValue')
            ->once()
            ->andReturn('Neuer Wert');

        $productField = \Mockery::mock(ProductField::class);
        $productField->shouldReceive('getIdentifier')
            ->once()
            ->andReturn($identifier);
        $productField->shouldReceive('getValue')
            ->once()
            ->andReturn('New value');
        $productField->shouldReceive('getTranslations')
            ->once()
            ->andReturn(new ArrayCollection([$translationData]));

        $message = \Mockery::mock(ProductUpdateMessage::class);
        $message->shouldReceive('getProductId')
            ->once()
            ->andReturn('123');
        $message->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection([$productField]));

        $product = \Mockery::mock(Product::class);
        $field = \Mockery::mock(Field::class);
        $translation = \Mockery::mock(FieldValueTranslation::class);

        $productRepository = \Mockery::mock(ProductRepository::class);
        $productRepository->shouldReceive('find')
            ->once()
            ->with('123')
            ->andReturn($product);

        $fieldResolver = \Mockery::mock(FieldResolver::class);
        $fieldResolver->shouldReceive('resolve')
            ->once()
            ->with($product, $identifier)
            ->andReturn($field);

        $field->shouldReceive('setValue')
            ->once()
            ->with('New value')
            ->andReturnSelf();

        $translationResolver = \Mockery::mock(FieldValueTranslationResolver::class);
        $translationResolver->shouldReceive('resolve')
            ->once()
            ->with($field, 'de_DE')
            ->andReturn($translation);

        $translation->shouldReceive('setValue')
            ->once()
            ->with('Neuer Wert')
            ->andReturnSelf();

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('persist')
            ->once()
            ->with($product);
        $entityManager->shouldReceive('flush')
            ->once();

        $handler = new ProductUpdateMessageHandler(
            $productRepository,
            $fieldResolver,
            $translationResolver,
            $entityManager,
        );

        $handler->handleProductUpdateMessage($message);
    }

    public function testHandleProductUpdateMessageSkipsUnresolvedField(): void
    {
        $identifier = \Mockery::mock(FieldTypeIdentifier::class);

        $productField = \Mockery::mock(ProductField::class);
        $productField->shouldReceive('getIdentifier')
            ->once()
            ->andReturn($identifier);

        $message = \Mockery::mock(ProductUpdateMessage::class);
        $message->shouldReceive('getProductId')
            ->once()
            ->andReturn('123');
        $message->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection([$productField]));

        $product = \Mockery::mock(Product::class);

        $productRepository = \Mockery::mock(ProductRepository::class);
        $productRepository->shouldReceive('find')
            ->once()
            ->with('123')
            ->andReturn($product);

        $fieldResolver = \Mockery::mock(FieldResolver::class);
        $fieldResolver->shouldReceive('resolve')
            ->once()
            ->with($product, $identifier)
            ->andReturnNull();

        $translationResolver = \Mockery::mock(FieldValueTranslationResolver::class);
        $translationResolver->shouldNotReceive('resolve');

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('persist')
            ->once()
            ->with($product);
        $entityManager->shouldReceive('flush')
            ->once();

        $handler = new ProductUpdateMessageHandler(
            $productRepository,
            $fieldResolver,
            $translationResolver,
            $entityManager,
        );

        $handler->handleProductUpdateMessage($message);
    }

    public function testHandleProductUpdateMessageSkipsUnresolvedTranslation(): void
    {
        $identifier = \Mockery::mock(FieldTypeIdentifier::class);

        $translationData = \Mockery::mock(FieldTranslation::class);
        $translationData->shouldReceive('getLocaleId')
            ->once()
            ->andReturn('de_DE');
        $translationData->shouldNotReceive('getValue');

        $productField = \Mockery::mock(ProductField::class);
        $productField->shouldReceive('getIdentifier')
            ->once()
            ->andReturn($identifier);
        $productField->shouldReceive('getValue')
            ->once()
            ->andReturn('New value');
        $productField->shouldReceive('getTranslations')
            ->once()
            ->andReturn(new ArrayCollection([$translationData]));

        $message = \Mockery::mock(ProductUpdateMessage::class);
        $message->shouldReceive('getProductId')
            ->once()
            ->andReturn('123');
        $message->shouldReceive('getFields')
            ->once()
            ->andReturn(new ArrayCollection([$productField]));

        $product = \Mockery::mock(Product::class);
        $field = \Mockery::mock(Field::class);

        $productRepository = \Mockery::mock(ProductRepository::class);
        $productRepository->shouldReceive('find')
            ->once()
            ->with('123')
            ->andReturn($product);

        $fieldResolver = \Mockery::mock(FieldResolver::class);
        $fieldResolver->shouldReceive('resolve')
            ->once()
            ->with($product, $identifier)
            ->andReturn($field);

        $field->shouldReceive('setValue')
            ->once()
            ->with('New value')
            ->andReturnSelf();

        $translationResolver = \Mockery::mock(FieldValueTranslationResolver::class);
        $translationResolver->shouldReceive('resolve')
            ->once()
            ->with($field, 'de_DE')
            ->andReturnNull();

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('persist')
            ->once()
            ->with($product);
        $entityManager->shouldReceive('flush')
            ->once();

        $handler = new ProductUpdateMessageHandler(
            $productRepository,
            $fieldResolver,
            $translationResolver,
            $entityManager,
        );

        $handler->handleProductUpdateMessage($message);
    }
}
