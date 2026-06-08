<?php

declare(strict_types=1);

namespace App\Tests\functional\api\product\get;

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

    // TODO: test: find products with no identifier
    // TODO: test deletion of identifiertype will remove related identifiers => integration test
    // TODO: test updating identifier will update products updated at

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
     */
    public function testGetProductsWithoutIdentifier(): void
    {
        SimpleProductStory::load();
        ProductFactory::createOne();

        $response = $this->client->request('GET', '/api/products');

        $this->assertResponseStatusCodeSame(200);

        // TODO: add assertions
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
