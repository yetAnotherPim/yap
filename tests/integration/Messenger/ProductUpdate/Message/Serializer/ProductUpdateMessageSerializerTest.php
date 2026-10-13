<?php

declare(strict_types=1);

namespace App\Tests\integration\Messenger\ProductUpdate\Message\Serializer;

use App\Messenger\ProductUpdate\Message\ProductUpdateMessage;
use App\Messenger\ProductUpdate\Message\Serializer\ProductUpdateMessageSerializer;
use App\Messenger\ProductUpdate\ProductUpdateMessageHandler;
use App\Messenger\ProductUpdate\Resolver\FieldResolver;
use App\Messenger\ProductUpdate\Resolver\FieldValueTranslationResolver;
use App\Repository\FieldTypeRepository;
use App\Repository\LocaleRepository;
use App\Repository\ProductRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

#[CoversClass(ProductUpdateMessageSerializer::class)]
#[UsesClass(FieldResolver::class)]
#[UsesClass(FieldTypeRepository::class)]
#[UsesClass(FieldValueTranslationResolver::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(ProductRepository::class)]
#[UsesClass(ProductUpdateMessage::class)]
#[UsesClass(ProductUpdateMessageHandler::class)]
final class ProductUpdateMessageSerializerTest extends KernelTestCase
{
    use InteractsWithMessenger;

    private ProductUpdateMessageSerializer $messageSerializer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->messageSerializer = new ProductUpdateMessageSerializer();
    }

    public function testDecodeSetsTypeWhenTypeIsMissing(): void
    {
        $envelope = $this->messageSerializer->decode([
            'body' => '{"productId":"123"}',
            'headers' => [],
        ]);

        $this->assertInstanceOf(
            ProductUpdateMessage::class,
            $envelope->getMessage(),
        );
        $this->assertSame('123', $envelope->getMessage()->getProductId());
    }

    public function testDecodePreservesExistingType(): void
    {
        $envelope = $this->messageSerializer->decode([
            'body' => '{}',
            'headers' => [
                'type' => \stdClass::class,
            ],
        ]);

        $this->assertInstanceOf(\stdClass::class, $envelope->getMessage());
    }

    public function testDecodeSetsTypeWhenHeadersAreMissing(): void
    {
        $envelope = $this->messageSerializer->decode([
            'body' => '{"productId":"123"}',
        ]);

        $this->assertInstanceOf(
            ProductUpdateMessage::class,
            $envelope->getMessage(),
        );
        $this->assertSame('123', $envelope->getMessage()->getProductId());
    }

    public function testQueueMessageWithoutTypeCanBeReceived(): void
    {
        $transport = $this->transport('product_update');
        $transport->throwExceptions();

        $productId = '00000000-0000-4000-8000-000000000001';

        $transport->send([
            'body' => json_encode([
                'productId' => $productId,
                'fields' => [],
            ], JSON_THROW_ON_ERROR),
            'headers' => [],
        ]);

        $transport->processOrFail();

        $transport->acknowledged()
            ->assertContains(ProductUpdateMessage::class);

        $dispatched = iterator_to_array($transport->dispatched());
        $this->assertNotEmpty($dispatched);

        $receivedMessage = end($dispatched)->getMessage();

        $this->assertInstanceOf(ProductUpdateMessage::class, $receivedMessage);
        $this->assertSame($productId, $receivedMessage->getProductId());
    }
}
