<?php

declare(strict_types=1);

namespace App\Tests\functional\api\products\PATCH;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use App\Entity\User\User;
use App\Localisation\Event\EventListener\LocaleListener;
use App\Localisation\Event\EventListener\LoginSuccessListener;
use App\Repository\FieldRepository;
use App\Repository\FieldTypeRepository;
use App\Repository\IdentifierRepository;
use App\Repository\IdentifierTypeRepository;
use App\Repository\LocaleRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Tests\helper\Trait\Api\AuthTrait;
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
#[UsesClass(Identifier::class)]
#[UsesClass(IdentifierRepository::class)]
#[UsesClass(IdentifierType::class)]
#[UsesClass(IdentifierTypeRepository::class)]
#[UsesClass(ProductType::class)]
#[UsesClass(LocaleListener::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(User::class)]
#[UsesClass(LoginSuccessListener::class)]
#[UsesClass(UserRepository::class)]
#[UsesClass(Locale::class)]
class ProductApiTest extends ApiTestCase
{
    use AuthTrait;

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

        $this->auth();

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
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
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
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
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

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestRequiresAuth(): void
    {
        $this->client->request('PATCH', '/api/products/1');

        $this->assertResponseStatusCodeSame(401);
    }
}
