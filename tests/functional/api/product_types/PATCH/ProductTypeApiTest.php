<?php

declare(strict_types=1);

namespace App\Tests\functional\api\product_types\PATCH;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Locale\Locale;
use App\Entity\Product\Type\ProductType;
use App\Entity\Product\Type\Translation\ProductTypeTitleTranslation;
use App\Repository\ProductTypeRepository;
use App\Repository\ProductTypeTitleTranslationRepository;
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
class ProductTypeApiTest extends ApiTestCase
{
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
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
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
                        'locale' => LocaleFactory::new(['id' => 'en_GB']),
                    ]),
                    ProductTypeTitleTranslationFactory::new([
                        'locale' => LocaleFactory::new(['id' => 'de_DE']),
                    ]),
                ],
            ]
        );

        $this->assertCount(2, $this->productTypeTitleTranslationRepository->findAll());

        $response = $this->client->request(
            'PATCH',
            '/api/product_types/'.$productType->getId(),
            [
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
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
}
