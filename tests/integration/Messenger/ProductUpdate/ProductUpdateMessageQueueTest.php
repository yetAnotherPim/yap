<?php

declare(strict_types=1);

namespace App\Tests\integration\Messenger\ProductUpdate;

use App\Entity\Product\Product;
use App\Messenger\ProductUpdate\Message\ProductUpdateMessage;
use App\Messenger\ProductUpdate\ProductUpdateMessageHandler;
use App\Messenger\ProductUpdate\Resolver\FieldResolver;
use App\Messenger\ProductUpdate\Resolver\FieldValueTranslationResolver;
use App\Repository\ProductRepository;
use App\Tests\TestData\Factory\Product\ProductFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

#[CoversClass(ProductUpdateMessageHandler::class)]
final class ProductUpdateMessageQueueTest extends KernelTestCase
{
    use InteractsWithMessenger;

    public function testDatabaseExceptionCausesMessageToBeRequeued(): void
    {
        $product = ProductFactory::createOne();

        $entityManager = \Mockery::mock(EntityManagerInterface::class);

        $entityManager
            ->shouldReceive('persist')
            ->once()
            ->with(\Mockery::type(Product::class));

        $entityManager
            ->shouldReceive('flush')
            ->once()
            ->andThrow(new \PDOException('Simulated database failure'));

        $this->registerHandler($entityManager);

        $transport = $this->transport('product_update');

        $transport->send(
            new ProductUpdateMessage((string) $product->getId()),
        );

        $transport->queue()->assertCount(1);

        $transport->process(1);

        // The message should be scheduled for redelivery.
        $transport->queue()->assertCount(1);
        $transport->queue()->first()->assertHasStamp(
            RedeliveryStamp::class,
        );
    }

    public function testMessageIsMovedToFailedQueueAfterMaxRetries(): void
    {
        $product = ProductFactory::createOne();

        $this->transport('product_update')->send(
            new ProductUpdateMessage((string) $product->getId()),
        );

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager
            ->shouldReceive('persist')
            ->never();

        $entityManager
            ->shouldReceive('flush')
            ->andThrow(new \RuntimeException('Database error'));

        $productRepository = static::getContainer()->get(ProductRepository::class);
        $this->assertInstanceOf(ProductRepository::class, $productRepository);
        $fieldResolver = static::getContainer()->get(FieldResolver::class);
        $this->assertInstanceOf(FieldResolver::class, $fieldResolver);
        $fieldValueTranslationResolver = static::getContainer()->get(FieldValueTranslationResolver::class);
        $this->assertInstanceOf(FieldValueTranslationResolver::class, $fieldValueTranslationResolver);

        static::getContainer()->set(
            ProductUpdateMessageHandler::class,
            new ProductUpdateMessageHandler(
                $productRepository,
                $fieldResolver,
                $fieldValueTranslationResolver,
                $entityManager,
            ),
        );

        $this->transport('product_update')->process(4);

        $transport = $this->transport('product_update');

        // All four attempts should have been rejected.
        $this->assertCount(4, $transport->rejected());

        // No messages should remain in the product update queue.
        $this->assertCount(0, $transport->queue());
    }

    public function testNonExistingProductIsIgnoredWithoutRequeue(): void
    {
        $entityManager = \Mockery::mock(EntityManagerInterface::class);

        $entityManager->shouldNotReceive('persist');
        $entityManager->shouldNotReceive('flush');

        $this->registerHandler($entityManager);

        $transport = $this->transport('product_update');
        $failedTransport = $this->transport('product_update_failed');

        $transport->send(
            new ProductUpdateMessage(
                Uuid::v4()->toRfc4122(),
            ),
        );

        $transport->queue()->assertCount(1);

        $transport->process(1);

        // Processing should succeed without persisting or flushing.
        $transport->queue()->assertEmpty();
        $transport->acknowledged()->assertContains(
            ProductUpdateMessage::class,
        );
        $transport->rejected()->assertEmpty();

        // No message should be sent to the failure transport.
        $failedTransport->queue()->assertEmpty();
    }

    private function registerHandler(
        EntityManagerInterface $entityManager,
    ): void {
        $container = static::getContainer();

        $productRepository = $container->get(ProductRepository::class);
        $this->assertInstanceOf(ProductRepository::class, $productRepository);

        $fieldResolver = $container->get(FieldResolver::class);
        $this->assertInstanceOf(FieldResolver::class, $fieldResolver);

        $fieldValueTranslationResolver = $container->get(FieldValueTranslationResolver::class);
        $this->assertInstanceOf(FieldValueTranslationResolver::class, $fieldValueTranslationResolver);

        $container->set(
            ProductUpdateMessageHandler::class,
            new ProductUpdateMessageHandler(
                $productRepository,
                $fieldResolver,
                $fieldValueTranslationResolver,
                $entityManager,
            ),
        );
    }
}
