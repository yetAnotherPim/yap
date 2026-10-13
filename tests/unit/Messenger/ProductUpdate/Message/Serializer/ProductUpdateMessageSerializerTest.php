<?php

declare(strict_types=1);

namespace App\Tests\unit\Messenger\ProductUpdate\Message\Serializer;

use App\Messenger\ProductUpdate\Message\ProductUpdateMessage;
use App\Messenger\ProductUpdate\Message\Serializer\ProductUpdateMessageSerializer;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ProductUpdateMessageSerializer::class)]
#[UsesClass(ProductUpdateMessage::class)]
class ProductUpdateMessageSerializerTest extends MockeryTestCase
{
    private ProductUpdateMessageSerializer $serializer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serializer = new ProductUpdateMessageSerializer();
    }

    public function testDecodeSetsTypeWhenTypeIsMissing(): void
    {
        $envelope = $this->serializer->decode([
            'body' => '{"productId":"123"}',
            'headers' => [],
        ]);

        $this->assertInstanceOf(ProductUpdateMessage::class, $envelope->getMessage());
        $this->assertSame('123', $envelope->getMessage()->getProductId());
    }

    public function testDecodePreservesExistingType(): void
    {
        $envelope = $this->serializer->decode([
            'body' => '{}',
            'headers' => [
                'type' => \stdClass::class,
            ],
        ]);

        $this->assertInstanceOf(\stdClass::class, $envelope->getMessage());
    }

    public function testDecodeSetsTypeWhenHeadersAreMissing(): void
    {
        $envelope = $this->serializer->decode([
            'body' => '{"productId":"123"}',
        ]);

        $this->assertInstanceOf(ProductUpdateMessage::class, $envelope->getMessage());
        $this->assertSame('123', $envelope->getMessage()->getProductId());
    }

    public function testGetMessageTypeReturnsExistingType(): void
    {
        $message = \Mockery::mock(ProductUpdateMessage::class);

        $messageType = $this->serializer->getMessageType([
            'body' => '{}',
            'headers' => [
                'type' => ProductUpdateMessage::class,
            ],
        ]);

        $this->assertInstanceOf(ProductUpdateMessage::class, $message);
        $this->assertSame(ProductUpdateMessage::class, $messageType);
    }

    public function testGetMessageTypeReturnsProductUpdateMessageWhenTypeIsMissing(): void
    {
        $message = \Mockery::mock(ProductUpdateMessage::class);

        $messageType = $this->serializer->getMessageType([
            'body' => '{}',
            'headers' => [],
        ]);

        $this->assertSame(ProductUpdateMessage::class, $messageType);
        $this->assertInstanceOf(ProductUpdateMessage::class, $message);
    }

    public function testGetMessageTypeReturnsProductUpdateMessageWhenHeadersAreMissing(): void
    {
        $message = \Mockery::mock(ProductUpdateMessage::class);

        $messageType = $this->serializer->getMessageType([
            'body' => '{}',
        ]);

        $this->assertSame(ProductUpdateMessage::class, $messageType);
        $this->assertInstanceOf(ProductUpdateMessage::class, $message);
    }
}
