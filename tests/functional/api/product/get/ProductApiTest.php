<?php

declare(strict_types=1);

namespace App\Tests\functional\api\product\get;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Tests\Factory\Product\ProductFactory;
use Zenstruck\Foundry\Test\Factories;

class ProductApiTest extends ApiTestCase {
    use Factories;

    private Client $client;

    protected function setUp(): void
    {
        $this->client = $client = static::createClient();

        parent::setUp();
    }


    public function testGetProduct(): void {
        $product = ProductFactory::createOne();


        $response = $this->client->request('GET', '/api/products/' . $product->getId());
        $this->assertResponseStatusCodeSame(200);
        var_dump($response->getContent());
    }
}
