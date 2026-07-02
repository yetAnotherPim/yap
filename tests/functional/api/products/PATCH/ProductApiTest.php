<?php

declare(strict_types=1);

namespace App\Tests\functional\api\products\PATCH;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Field\Type\Section\FieldSection;
use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Repository\FieldRepository;
use App\Repository\FieldTypeRepository;
use App\Repository\IdentifierRepository;
use App\Repository\IdentifierTypeRepository;
use App\Repository\ProductRepository;
use App\Tests\TestData\Story\SimpleProductStory;
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
#[UsesClass(FieldSection::class)]
#[UsesClass(Identifier::class)]
#[UsesClass(IdentifierRepository::class)]
#[UsesClass(IdentifierType::class)]
#[UsesClass(IdentifierTypeRepository::class)]
class ProductApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private Client $client;

    private ProductRepository $productRepository;

    private IdentifierRepository $identifierRepository;

    private FieldRepository $fieldRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = $this->getContainer();

        $productRepository = $container->get(ProductRepository::class);
        $this->assertInstanceOf(ProductRepository::class, $productRepository);

        $identifierRepository = $container->get(IdentifierRepository::class);
        $this->assertInstanceOf(IdentifierRepository::class, $identifierRepository);

        $fieldRepository = $container->get(FieldRepository::class);
        $this->assertInstanceOf(FieldRepository::class, $fieldRepository);

        $this->productRepository = $productRepository;
        $this->identifierRepository = $identifierRepository;
        $this->fieldRepository = $fieldRepository;

        parent::setUp();
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testPatchRemovingFields(): void
    {
        /** @phpstan-var Product $product */
        $product = SimpleProductStory::get('simple_product');

        $this->assertCount(2, $product->getFields());

        $this->client->request(
            'PATCH',
            '/api/products/'.$product->getId(),
            [
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
                'body' => '{
                                "fields":[]
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $product = $this->productRepository->find($product->getId());

        $this->assertInstanceOf(Product::class, $product);
        $this->assertCount(0, $product->getFields());

        $this->assertCount(0, $this->fieldRepository->findAll());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testPatchRemovingIdentifiers(): void
    {
        SimpleProductStory::load();
        /** @phpstan-var Product $product */
        $product = SimpleProductStory::get('simple_product');

        $this->assertCount(2, $product->getIdentifiers());

        $this->client->request(
            'PATCH',
            '/api/products/'.$product->getId(),
            [
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
                'body' => '{
                                "identifiers":[]
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(200);

        $product = $this->productRepository->find($product->getId());

        $this->assertInstanceOf(Product::class, $product);
        $this->assertCount(0, $product->getIdentifiers());

        $this->assertCount(0, $this->identifierRepository->findAll());
    }
}
