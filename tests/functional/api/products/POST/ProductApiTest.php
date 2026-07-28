<?php

declare(strict_types=1);

namespace App\Tests\functional\api\products\POST;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use App\Localisation\Event\EventListener\LocaleListener;
use App\Repository\FieldRepository;
use App\Repository\FieldTypeRepository;
use App\Repository\IdentifierRepository;
use App\Repository\IdentifierTypeRepository;
use App\Repository\LocaleRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductTypeRepository;
use App\Tests\TestData\Factory\Product\Field\Type\FieldTypeFactory;
use App\Tests\TestData\Factory\Product\Identifier\Type\IdentifierTypeFactory;
use App\Tests\TestData\Factory\Product\Type\ProductTypeFactory;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(Product::class)]
#[CoversClass(ProductRepository::class)]
#[UsesClass(Field::class)]
#[UsesClass(FieldRepository::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(FieldTypeRepository::class)]
#[UsesClass(Identifier::class)]
#[UsesClass(IdentifierRepository::class)]
#[UsesClass(IdentifierType::class)]
#[UsesClass(IdentifierTypeRepository::class)]
#[UsesClass(ProductType::class)]
#[UsesClass(ProductTypeRepository::class)]
#[UsesClass(LocaleListener::class)]
#[UsesClass(LocaleRepository::class)]
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
        /** @phpstan-var ProductType $productType */
        $productType = ProductTypeFactory::createOne();

        /** @phpstan-var IdentifierType $identifierType */
        $identifierType = IdentifierTypeFactory::createOne();

        /** @phpstan-var FieldType $fieldType */
        $fieldType = FieldTypeFactory::createOne();

        $this->client->request(
            'POST',
            '/api/products',
            [
                'body' => '{
                                "productType":"/api/product_types/'.$productType->getId().'",
                                "identifiers":[
                                    {
                                        "value":"123456",
                                        "type":"/api/identifier_types/'.$identifierType->getId().'"
                                    }
                                ],
                                "fields":[
                                    {
                                        "value":"123456",
                                        "type":"/api/field_types/'.$fieldType->getId().'"
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
        $this->assertCount(1, $product->getFields());

        $identifier = $product->getIdentifiers()->first();
        $this->assertInstanceOf(Identifier::class, $identifier);
        $this->assertEquals('123456', $identifier->getValue());

        $field = $product->getFields()->first();
        $this->assertInstanceOf(Field::class, $field);
        $this->assertEquals('123456', $field->getValue());
    }
}
