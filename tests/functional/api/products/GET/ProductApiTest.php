<?php

declare(strict_types=1);

namespace App\Tests\functional\api\products\GET;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Product\Product;
use App\Tests\TestData\Factory\Product\ProductFactory;
use App\Tests\TestData\Story\ProductsSharingIdentifierStory;
use App\Tests\TestData\Story\SimpleProductStory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Zenstruck\Foundry\Test\Factories;

#[CoversClass(Product::class)]
class ProductApiTest extends ApiTestCase
{
    use Factories;

    protected static ?bool $alwaysBootKernel = true;

    private Client $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

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

        $this->client->request('GET', '/api/products/'.$product->getId());

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

        $this->client->request('GET', '/api/products');

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

        $this->client->request('GET', '/api/products');

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);

        $jsonSchema = file_get_contents(__DIR__.'/../../../../JsonSchema/products.schema.json');

        $this->assertIsString($jsonSchema);
        $this->assertMatchesJsonSchema($jsonSchema);

        $this->client->request('GET', '/api/products?createdAt[gte]=2022-02-21');
        $this->assertJsonContains(['totalItems' => 2]);

        $this->client->request('GET', '/api/products?createdAt[gte]=2022-02-22');
        $this->assertJsonContains(['totalItems' => 1]);

        $this->client->request('GET', '/api/products?createdAt[gt]=2022-02-22');
        $this->assertJsonContains(['totalItems' => 0]);

        $this->client->request('GET', '/api/products?updatedAt[gte]=2022-02-21');
        $this->assertJsonContains(['totalItems' => 2]);

        $this->client->request('GET', '/api/products?updatedAt[gte]=2022-02-22');
        $this->assertJsonContains(['totalItems' => 1]);

        $this->client->request('GET', '/api/products?updatedAt[gt]=2022-02-22');
        $this->assertJsonContains(['totalItems' => 0]);
    }
}
