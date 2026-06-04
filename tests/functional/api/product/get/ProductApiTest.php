<?php

declare(strict_types=1);

namespace App\Tests\functional\api\product\get;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Identifier\Type\Enum\DataType;
use App\Tests\Factory\Identifier\IdentifierFactory;
use App\Tests\Factory\Identifier\Type\IdentifierTypeFactory;
use App\Tests\Factory\Product\ProductFactory;
use Zenstruck\Foundry\Attribute\ResetDatabase;
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

    // TODO: api test multiple products with multiple identifier, some sharing same identifier (condition -> used, new)
    // TODO: test: find products with no identifier

    public function testGetProduct(): void
    {
        //TODO: fix product gneration -> each identifier is duplicating product
        $product = ProductFactory::createOne(
            [
                'identifiers' => [
                    IdentifierFactory::createOne(
                        [
                            'value' => '04012345123456',
                            'type' => IdentifierTypeFactory::createOne(['title' => 'GTIN', 'dataType' => DataType::String]),
                        ]
                    ),
                    IdentifierFactory::createOne(
                        [
                            'value' => '12345',
                            'type' => IdentifierTypeFactory::createOne(['title' => 'Internal', 'dataType' => DataType::Integer]),
                        ]
                    ),
                ],
            ]
        );

        //$response = $this->client->request('GET', '/api/products/'.$product->getId());
        $response = $this->client->request('GET', '/api/products');
        $this->assertResponseStatusCodeSame(200);
        var_dump($response->getContent());
        //TODO: add assertions
    }

    public function testGetProducts(): void
    {
        $product = ProductFactory::createMany(2,
            [
                'identifiers' => [
                    IdentifierFactory::createOne(
                        [
                            'value' => '04012345123456',
                            'type' => IdentifierTypeFactory::createOne(['title' => 'GTIN', 'dataType' => DataType::String]),
                        ]
                    ),
                    IdentifierFactory::createOne(
                        [
                            'value' => '12345',
                            'type' => IdentifierTypeFactory::createOne(['title' => 'Internal', 'dataType' => DataType::Integer]),
                        ]
                    ),
                ],
            ]
        );

        $response = $this->client->request('GET', '/api/products');
        $this->assertResponseStatusCodeSame(200);

        //TODO: add assertions
    }
}
