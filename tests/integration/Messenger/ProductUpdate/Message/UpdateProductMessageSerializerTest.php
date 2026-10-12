<?php

declare(strict_types=1);

namespace App\Tests\integration\Messenger\ProductUpdate\Message;

use App\Messenger\ProductUpdate\Message\Dto\FieldIdentifier;
use App\Messenger\ProductUpdate\Message\Dto\FieldTranslation;
use App\Messenger\ProductUpdate\Message\Dto\ProductField;
use App\Messenger\ProductUpdate\Message\Enum\ProductFieldIdentifierType;
use App\Messenger\ProductUpdate\Message\UpdateProductMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(UpdateProductMessage::class)]
#[UsesClass(ProductField::class)]
#[UsesClass(FieldIdentifier::class)]
#[UsesClass(FieldTranslation::class)]
#[UsesClass(ProductFieldIdentifierType::class)]
class UpdateProductMessageSerializerTest extends KernelTestCase
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
            UpdateProductMessage::class,
            'json',
        );

        $this->assertInstanceOf(UpdateProductMessage::class, $message);
        $this->assertSame('product-1', $message->getProductId());

        $fields = $message->getFields();

        $this->assertCount(1, $fields);

        $field = $fields->first();

        $this->assertInstanceOf(ProductField::class, $field);
        $this->assertSame('test', $field->getValue());

        $identifier = $field->getIdentifier();

        $this->assertSame(ProductFieldIdentifierType::ID, $identifier->getType());
        $this->assertSame('field-1', $identifier->getValue());

        $translations = $field->getTranslations();

        $this->assertCount(1, $translations);

        $translation = $translations->first();

        $this->assertInstanceOf(FieldTranslation::class, $translation);
        $this->assertSame('de_DE', $translation->getLocaleId());
        $this->assertSame('Test', $translation->getValue());
    }
}
