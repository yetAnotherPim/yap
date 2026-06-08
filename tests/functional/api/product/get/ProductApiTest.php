<?php

declare(strict_types=1);

namespace App\Tests\functional\api\product\get;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Tests\Factory\Product\ProductFactory;
use App\Tests\Story\ProductsSharingIdentifierStory;
use App\Tests\Story\SimpleProductStory;
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
    // TODO: test deletion of identifiertype will remove related identifiers
    // TODO: test updating identifier will update products updated at

    public function testGetProduct(): void
    {
        SimpleProductStory::load();
        $product = SimpleProductStory::get('simple_product');

        $this->client->request('GET', '/api/products/'.$product->getId());

        $this->assertResponseStatusCodeSame(200);
        $this->assertMatchesJsonSchema(file_get_contents(__DIR__.'/../../../../JsonSchema/product.schema.json'));
    }

    public function testGetProductsWithoutIdentifier(): void
    {
        SimpleProductStory::load();
        ProductFactory::createOne();

        $response = $this->client->request('GET', '/api/products');

        $this->assertResponseStatusCodeSame(200);

        // TODO: add assertions
    }

    public function testGetProducts(): void
    {
        ProductsSharingIdentifierStory::load();

        $response = $this->client->request('GET', '/api/products');

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(2, $response->toArray()['member']);
        $this->assertMatchesJsonSchema(file_get_contents(__DIR__.'/../../../../JsonSchema/products.schema.json'));
    }
}
