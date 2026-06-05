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

        $response = $this->client->request('GET', '/api/products/'.$product->getId());

        $this->assertResponseStatusCodeSame(200);

        $expectedResult = <<<JSON
        {
          "@context":"/api/contexts/Product",
          "@id":"/api/products/019e976e-3803-7c38-8ff9-6d1f74766f85",
          "@type":"Product",
          "identifiers":[
            {
              "@id":"/api/identifiers/019e976e-3804-7f41-8232-a41330146a42",
              "@type":"Identifier",
              "type":{"@id":"/api/identifier_types/019e976e-3804-79e9-8232-a4132fa2a7b6","@type":"IdentifierType","title":"GTIN"},
              "value":"04012345123456",
              "typedValue":"04012345123456"},
            {
              "@id":"/api/identifiers/019e976e-3805-7b74-8f3f-0973f64d05dc",
              "@type":"Identifier",
              "type":{"@id":"/api/identifier_types/019e976e-3805-787c-8f3f-0973f5c1af66","@type":"IdentifierType","title":"Internal"},
              "value":"12345",
              "typedValue":12345}
          ],
          "createdAt":"2020-11-22T12:30:56+00:00"}
        JSON;


        // TODO: add assertions
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
        // TODO: add assertions
    }
}
