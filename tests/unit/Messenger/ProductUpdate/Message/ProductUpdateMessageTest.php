<?php

declare(strict_types=1);

namespace App\Tests\unit\Messenger\ProductUpdate\Message;

use App\Messenger\ProductUpdate\Message\Dto\ProductField;
use App\Messenger\ProductUpdate\Message\ProductUpdateMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ProductUpdateMessage::class)]
class ProductUpdateMessageTest extends MockeryTestCase
{
    private const string PRODUCT_ID = '550e8400-e29b-41d4-a716-446655440000';

    public function testConstructorInitializesProperties(): void
    {
        $message = new ProductUpdateMessage(self::PRODUCT_ID);

        $this->assertSame(self::PRODUCT_ID, $message->getProductId());
        $this->assertInstanceOf(ArrayCollection::class, $message->getFields());
        $this->assertCount(0, $message->getFields());
    }

    public function testSetProductId(): void
    {
        $message = new ProductUpdateMessage(self::PRODUCT_ID);
        $newProductId = '550e8400-e29b-41d4-a716-446655440001';

        $result = $message->setProductId($newProductId);

        $this->assertSame($message, $result);
        $this->assertSame($newProductId, $message->getProductId());
    }

    public function testSetFields(): void
    {
        $message = new ProductUpdateMessage(self::PRODUCT_ID);
        $fields = \Mockery::mock(Collection::class);

        $result = $message->setFields($fields);

        $this->assertSame($message, $result);
        $this->assertSame($fields, $message->getFields());
    }

    public function testGetFieldsReturnsConfiguredCollection(): void
    {
        $message = new ProductUpdateMessage(self::PRODUCT_ID);
        $field = \Mockery::mock(ProductField::class);

        /** @var Collection<int, ProductField> $fields */
        $fields = new ArrayCollection([$field]);

        $message->setFields($fields);

        $this->assertSame($fields, $message->getFields());
        $this->assertSame($field, $message->getFields()->first());
    }

    public function testAddField(): void
    {
        $message = new ProductUpdateMessage(self::PRODUCT_ID);
        $field = \Mockery::mock(ProductField::class);

        $result = $message->addField($field);

        $this->assertSame($message, $result);
        $this->assertTrue($message->getFields()->contains($field));
    }

    public function testRemoveField(): void
    {
        $message = new ProductUpdateMessage(self::PRODUCT_ID);
        $field = \Mockery::mock(ProductField::class);

        $message->addField($field);

        $result = $message->removeField($field);

        $this->assertSame($message, $result);
        $this->assertFalse($message->getFields()->contains($field));
        $this->assertCount(0, $message->getFields());
    }
}
