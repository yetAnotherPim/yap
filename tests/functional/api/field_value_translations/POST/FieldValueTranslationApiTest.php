<?php

declare(strict_types=1);

namespace App\Tests\functional\api\field_value_translation\POST;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Field\Type\Section\FieldSection;
use App\Entity\Product\Product;
use App\Repository\FieldRepository;
use App\Repository\FieldTypeRepository;
use App\Repository\FieldValueTranslationRepository;
use App\Repository\LocaleRepository;
use App\Repository\ProductRepository;
use App\Tests\TestData\Factory\Locale\LocaleFactory;
use App\Tests\TestData\Factory\Product\Field\FieldFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(FieldValueTranslation::class)]
#[CoversClass(FieldValueTranslationRepository::class)]
#[UsesClass(Field::class)]
#[UsesClass(FieldRepository::class)]
#[UsesClass(Locale::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(FieldSection::class)]
#[UsesClass(Product::class)]
#[UsesClass(ProductRepository::class)]
#[UsesClass(FieldTypeRepository::class)]
class FieldValueTranslationApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private Client $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        parent::setUp();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testPost(): void
    {
        $field = FieldFactory::createOne();
        $locale = LocaleFactory::createOne(['id' => 'en_GB']);

        $product = $field->getProduct();
        $updatedAt = $product->getUpdatedAt();

        $response = $this->client->request(
            'POST',
            '/api/field_value_translations',
            [
                'body' => '{
                                "value":"123456",
                                "field":"/api/fields/'.$field->getId().'",
                                "locale":"/api/locales/'.$locale->getId().'"
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['value' => '123456']);
        $this->assertStringContainsString('"createdAt":', $response->getContent());
        $this->assertStringNotContainsString('"updatedAt":', $response->getContent());

        /** @phpstan-var string $id */
        $id = $response->toArray()['id'];

        /** test products contains fields */
        $response = $this->client->request('GET', '/api/products/'.$field->getProduct()->getId());
        $this->assertStringContainsString($id, $response->getContent());
        $this->assertStringContainsString('"locale":"\/api\/locales\/en_GB","value":"123456"', $response->getContent());

        $this->assertNotEquals($updatedAt, $product->getUpdatedAt());
    }
}
