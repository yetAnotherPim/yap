<?php

declare(strict_types=1);

namespace App\Tests\functional\api\products\POST;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Identifier\Identifier;
use App\Entity\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Repository\ProductRepository;
use App\Tests\Factory\Identifier\Type\IdentifierTypeFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class ProductApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private Client $client;

    private ProductRepository $productRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = $this->getContainer();

        $productRepository = $container->get(ProductRepository::class);

        $this->assertInstanceOf(ProductRepository::class, $productRepository);

        $this->productRepository = $productRepository;

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
        // TODO: test orphan removal via api deletion as in integration test

        /** @phpstan-var IdentifierType $identifierType */
        $identifierType = IdentifierTypeFactory::createOne();

        $this->client->request(
            'POST',
            '/api/products',
            [
                'body' => '{
                                "identifiers":[
                                    {
                                        "value":"123456",
                                        "type":"/api/identifier_types/'.$identifierType->getId().'"
                                    }
                                ]
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(201);

        $products = new ArrayCollection($this->productRepository->findAll());

        $this->assertCount(1, $products);
        $product = $products->first();

        $this->assertInstanceOf(Product::class, $product);
        $this->assertCount(1, $product->getIdentifiers());

        $identifier = $product->getIdentifiers()->first();
        $this->assertInstanceOf(Identifier::class, $identifier);
        $this->assertEquals('123456', $identifier->getValue());
    }
}
