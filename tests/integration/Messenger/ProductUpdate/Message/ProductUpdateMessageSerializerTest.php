<?php

declare(strict_types=1);

namespace App\Tests\integration\Messenger\ProductUpdate\Message;

use App\Messenger\ProductUpdate\Message\Dto\FieldTranslation;
use App\Messenger\ProductUpdate\Message\Dto\FieldTypeIdentifier;
use App\Messenger\ProductUpdate\Message\Dto\ProductField;
use App\Messenger\ProductUpdate\Message\Enum\FieldTypeIdentifierType;
use App\Messenger\ProductUpdate\Message\ProductUpdateMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(ProductUpdateMessage::class)]
#[UsesClass(ProductField::class)]
#[UsesClass(FieldTypeIdentifier::class)]
#[UsesClass(FieldTranslation::class)]
#[UsesClass(FieldTypeIdentifierType::class)]
class ProductUpdateMessageSerializerTest extends KernelTestCase
{
    public function testDeserializesCollections(): void
    {
        self::bootKernel();

        /** @var SerializerInterface $serializer */
        $serializer = self::getContainer()->get(SerializerInterface::class);

        $json = <<<'JSON'
        {
            "productId": "product-1",
            "fields": [
                {
                    "identifier": {
                        "type": "id",
                        "value": "field-1"
                    },
                    "value": "test",
                    "translations": [
                        {
                            "localeId": "de_DE",
                            "value": "Test"
                        }
                    ]
                }
            ]
        }
        JSON;

        $message = $serializer->deserialize(
            $json,
            ProductUpdateMessage::class,
            'json',
        );

        $this->assertInstanceOf(ProductUpdateMessage::class, $message);
        $this->assertSame('product-1', $message->getProductId());

        $fields = $message->getFields();

        $this->assertCount(1, $fields);

        $field = $fields->first();

        $this->assertInstanceOf(ProductField::class, $field);
        $this->assertSame('test', $field->getValue());

        $identifier = $field->getIdentifier();

        $this->assertSame(FieldTypeIdentifierType::ID, $identifier->getType());
        $this->assertSame('field-1', $identifier->getValue());

        $translations = $field->getTranslations();

        $this->assertCount(1, $translations);

        $translation = $translations->first();

        $this->assertInstanceOf(FieldTranslation::class, $translation);
        $this->assertSame('de_DE', $translation->getLocaleId());
        $this->assertSame('Test', $translation->getValue());
    }
}
