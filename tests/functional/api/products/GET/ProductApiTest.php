<?php

declare(strict_types=1);

namespace App\Tests\functional\api\products\GET;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use App\Entity\User\User;
use App\Localisation\Event\EventListener\LocaleListener;
use App\Localisation\Event\EventListener\LoginSuccessListener;
use App\Repository\LocaleRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Tests\helper\Trait\Api\AuthTrait;
use App\Tests\TestData\Factory\Product\ProductFactory;
use App\Tests\TestData\Story\MultipleProductsStory;
use App\Tests\TestData\Story\ProductsSharingIdentifierStory;
use App\Tests\TestData\Story\ProductsWithFieldsAndTranslationsStory;
use App\Tests\TestData\Story\SimpleProductStory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Zenstruck\Foundry\Attribute\ResetDatabase;

#[CoversClass(Product::class)]
#[CoversClass(ProductRepository::class)]
#[UsesClass(Identifier::class)]
#[UsesClass(IdentifierType::class)]
#[UsesClass(Field::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(Locale::class)]
#[UsesClass(FieldValueTranslation::class)]
#[UsesClass(ProductType::class)]
#[UsesClass(LocaleListener::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(User::class)]
#[UsesClass(LoginSuccessListener::class)]
#[UsesClass(UserRepository::class)]
#[ResetDatabase]
class ProductApiTest extends ApiTestCase
{
    use AuthTrait;

    protected static ?bool $alwaysBootKernel = true;

    private Client $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->auth();

        parent::setUp();
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testGetProduct(): void
    {
        SimpleProductStory::load();
        /** @phpstan-var Product $product */
        $product = SimpleProductStory::get('simple_product');

        $this->client->request(
            'GET',
            '/api/products/'.$product->getId(),
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $jsonSchema = file_get_contents(__DIR__.'/../../../../JsonSchema/product.schema.json');

        $this->assertIsString($jsonSchema);
        $this->assertMatchesJsonSchema($jsonSchema);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetProducts(): void
    {
        ProductsSharingIdentifierStory::load();

        $this->client->request(
            'GET',
            '/api/products',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);

        $jsonSchema = file_get_contents(__DIR__.'/../../../../JsonSchema/products.schema.json');

        $this->assertIsString($jsonSchema);
        $this->assertMatchesJsonSchema($jsonSchema);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetProductsWithDateFilter(): void
    {
        ProductFactory::createOne(['createdAt' => new \DateTimeImmutable('2022-02-22'), 'updatedAt' => new \DateTimeImmutable('2022-02-22')]);
        ProductFactory::createOne(['createdAt' => new \DateTimeImmutable('2022-02-21'), 'updatedAt' => new \DateTimeImmutable('2022-02-21')]);
        ProductFactory::createOne(['createdAt' => new \DateTimeImmutable('2022-02-21 12:00:00'), 'updatedAt' => new \DateTimeImmutable('2022-02-21 12:00:00')]);

        $response = $this->client->request(
            'GET',
            '/api/products',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 3]);

        $jsonSchema = file_get_contents(__DIR__.'/../../../../JsonSchema/products.schema.json');

        $this->assertIsString($jsonSchema);
        $this->assertMatchesJsonSchema($jsonSchema);

        $this->client->request(
            'GET',
            '/api/products?createdAt[gte]=2022-02-21',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 3]);

        $this->client->request(
            'GET',
            '/api/products?createdAt[gte]=2022-02-21 12:00:00',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 2]);

        $this->client->request(
            'GET',
            '/api/products?createdAt[gte]=2022-02-22',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 1]);

        $this->client->request(
            'GET',
            '/api/products?createdAt[gte]=2022-02-23',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 0]);

        $this->client->request(
            'GET',
            '/api/products?createdAt[lte]=2022-02-21',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 1]);

        $this->client->request(
            'GET',
            '/api/products?createdAt[lte]=2022-02-21 12:00:00',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 2]);

        $this->client->request(
            'GET',
            '/api/products?createdAt[lte]=2022-02-22',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 3]);

        $this->client->request(
            'GET',
            '/api/products?createdAt[lt]=2022-02-22',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 2]);

        $this->client->request(
            'GET',
            '/api/products?createdAt[lte]=2022-02-23',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 3]);

        $this->client->request(
            'GET',
            '/api/products?updatedAt[gte]=2022-02-21',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 3]);

        $this->client->request(
            'GET',
            '/api/products?updatedAt[gte]=2022-02-21 12:00:00',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 2]);

        $this->client->request(
            'GET',
            '/api/products?updatedAt[gte]=2022-02-22',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 1]);

        $this->client->request(
            'GET',
            '/api/products?updatedAt[gte]=2022-02-23',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 0]);

        $this->client->request(
            'GET',
            '/api/products?updatedAt[lte]=2022-02-21',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 1]);

        $this->client->request(
            'GET',
            '/api/products?updatedAt[lte]=2022-02-21 12:00:00',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 2]);

        $this->client->request(
            'GET',
            '/api/products?updatedAt[lte]=2022-02-22',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 3]);

        $this->client->request(
            'GET',
            '/api/products?updatedAt[lt]=2022-02-22',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 2]);

        $this->client->request(
            'GET',
            '/api/products?updatedAt[lte]=2022-02-23',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 3]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetProductsWithoutIdentifier(): void
    {
        SimpleProductStory::load();
        ProductFactory::createOne([
            'identifiers' => [],
        ]);

        $response = $this->client->request(
            'GET',
            '/api/products?hasIdentifier=true',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertJsonContains(['totalItems' => 1]);
        $this->assertStringContainsString('"identifiers":[{"@id":', $response->getContent());
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->request(
            'GET',
            '/api/products?hasIdentifier=false',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );

        $this->assertJsonContains(['totalItems' => 1]);
        $this->assertStringContainsString('"identifiers":[]', $response->getContent());
        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetProductsViaIdentifier(): void
    {
        MultipleProductsStory::load();

        $this->client->request(
            'GET',
            '/api/products?identifierIs=1111',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 1]);

        $this->client->request(
            'GET',
            '/api/products?identifierLike=0111',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);

        $this->client->request(
            'GET',
            '/api/products?identifierIs[]=1111&identifierIs[]=2222',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetProductsViaIdentifierAndType(): void
    {
        MultipleProductsStory::load();

        $this->client->request(
            'GET',
            '/api/products?identifierLike=1111&identifierType=Internal',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 1]);

        $this->client->request(
            'GET',
            '/api/products?identifierLike=01111&identifierType=GTIN',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);

        $this->client->request(
            'GET',
            '/api/products?identifierLike[]=1111&identifierLike[]=2222&identifierType=Internal',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetProductsViaFieldValueAndType(): void
    {
        ProductsWithFieldsAndTranslationsStory::load();

        $this->client->request(
            'GET',
            '/api/products?fieldValueIs=Product1',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]);
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);

        $this->client->request(
            'GET',
            '/api/products?fieldValueIs=Product1&fieldTypeCode=product_name',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 1]);

        $this->client->request(
            'GET',
            '/api/products?fieldValueIs[]=Product1&fieldValueIs[]=Product2&fieldTypeCode=product_name',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);

        $this->client->request(
            'GET',
            '/api/products?fieldValueIs=Product1&fieldTypeCode=similar_to',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 1]);

        $this->client->request(
            'GET',
            '/api/products?fieldValueLike=Product&fieldTypeCode=product_name',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetProductsViaTranslatedFieldValueAndType(): void
    {
        ProductsWithFieldsAndTranslationsStory::load();

        $this->client->request(
            'GET',
            '/api/products?fieldTranslatedValueIs[]=Produkt1&fieldTranslatedValueIs[]=Producto1&fieldTypeCode=product_name',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 1]);

        $jsonSchema = file_get_contents(__DIR__.'/../../../../JsonSchema/products.schema.json');

        $this->assertIsString($jsonSchema);
        $this->assertMatchesJsonSchema($jsonSchema);

        $this->client->request(
            'GET',
            '/api/products?fieldTranslatedValueIs=Produkt1&fieldTypeCode=product_name',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 1]);

        $this->client->request(
            'GET',
            '/api/products?fieldTranslatedValueIs=Produkt1&fieldTypeCode=product_name&locale=es_ES',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 0]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestRequiresAuth(): void
    {
        $this->client->request('GET', '/api/products');

        $this->assertResponseStatusCodeSame(401);
    }
}
