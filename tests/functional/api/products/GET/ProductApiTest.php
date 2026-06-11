<?php

declare(strict_types=1);

namespace App\Tests\functional\api\products\GET;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Product\Product;
use App\Tests\Factory\Product\ProductFactory;
use App\Tests\Story\ProductsSharingIdentifierStory;
use App\Tests\Story\SimpleProductStory;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Zenstruck\Foundry\Test\Factories;

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

        $response = $this->client->request('GET', '/api/products?hasIdentifiers=1');
        $this->assertJsonContains(['totalItems' => 1]);
        $this->assertStringContainsString('"identifiers":[{"@id":', $response->getContent());
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->request('GET', '/api/products?hasIdentifiers=0');

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
}
