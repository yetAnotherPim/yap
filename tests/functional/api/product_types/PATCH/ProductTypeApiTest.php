<?php

declare(strict_types=1);

namespace App\Tests\functional\api\product_types\PATCH;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Locale\Locale;
use App\Entity\Product\Type\ProductType;
use App\Entity\Product\Type\Translation\ProductTypeTitleTranslation;
use App\Entity\User\User;
use App\Localisation\Event\EventListener\LocaleListener;
use App\Localisation\Event\EventListener\LoginSuccessListener;
use App\Repository\LocaleRepository;
use App\Repository\ProductTypeRepository;
use App\Repository\ProductTypeTitleTranslationRepository;
use App\Repository\UserRepository;
use App\Tests\helper\Trait\Api\AuthTrait;
use App\Tests\TestData\Factory\Locale\LocaleFactory;
use App\Tests\TestData\Factory\Product\Type\ProductTypeFactory;
use App\Tests\TestData\Factory\Product\Type\Translation\ProductTypeTitleTranslationFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(ProductType::class)]
#[CoversClass(ProductTypeRepository::class)]
#[UsesClass(Locale::class)]
#[UsesClass(ProductTypeTitleTranslation::class)]
#[UsesClass(ProductTypeTitleTranslationRepository::class)]
#[UsesClass(LocaleListener::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(User::class)]
#[UsesClass(LoginSuccessListener::class)]
#[UsesClass(UserRepository::class)]
class ProductTypeApiTest extends ApiTestCase
{
    use AuthTrait;

    protected static ?bool $alwaysBootKernel = true;

    private Client $client;

    private ProductTypeTitleTranslationRepository $productTypeTitleTranslationRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = $this->getContainer();

        $productTypeTitleTranslationRepository = $container->get(ProductTypeTitleTranslationRepository::class);
        $this->assertInstanceOf(ProductTypeTitleTranslationRepository::class, $productTypeTitleTranslationRepository);

        $this->productTypeTitleTranslationRepository = $productTypeTitleTranslationRepository;

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
    public function testPatch(): void
    {
        $productType = ProductTypeFactory::createOne(['title' => 'Generic product', 'code' => 'generic_ product']);

        $response = $this->client->request(
            'PATCH',
            '/api/product_types/'.$productType->getId(),
            [
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
                'body' => '{
                                "title":"Awesome product",
                                "code":"awesome_product"
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['title' => 'Awesome product']);
        $this->assertJsonContains(['code' => 'awesome_product']);
        $this->assertStringContainsString('"createdAt":', $response->getContent());
        $this->assertStringContainsString('"updatedAt":', $response->getContent());
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testPatchRemovingTranslation(): void
    {
        $productType = ProductTypeFactory::createOne(
            [
                'title' => 'Something',
                'code' => 'something',
                'translations' => [
                    ProductTypeTitleTranslationFactory::new([
                        'locale' => LocaleFactory::findOrCreate(['id' => 'en_GB']),
                    ]),
                    ProductTypeTitleTranslationFactory::new([
                        'locale' => LocaleFactory::findOrCreate(['id' => 'de_DE']),
                    ]),
                ],
            ]
        );

        $this->assertCount(2, $this->productTypeTitleTranslationRepository->findAll());

        $response = $this->client->request(
            'PATCH',
            '/api/product_types/'.$productType->getId(),
            [
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
                'body' => '{
                                "translations":[]
                            }',
            ]
        );

        $this->assertCount(0, $this->productTypeTitleTranslationRepository->findAll());

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['title' => 'Something']);
        $this->assertJsonContains(['code' => 'something']);
        $this->assertStringContainsString('"createdAt":', $response->getContent());
        $this->assertStringContainsString('"updatedAt":', $response->getContent());
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestRequiresAuth(): void
    {
        $this->client->request('PATCH', '/api/product_types/1');

        $this->assertResponseStatusCodeSame(401);
    }
}
