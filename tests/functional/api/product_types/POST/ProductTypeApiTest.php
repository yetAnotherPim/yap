<?php

declare(strict_types=1);

namespace App\Tests\functional\api\product_types\POST;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Abstract\RelatedTimestampableEntity;
use App\Entity\Locale\Locale;
use App\Entity\Product\Type\ProductType;
use App\Entity\Product\Type\Translation\ProductTypeTitleTranslation;
use App\Localisation\Event\EventListener\LocaleListener;
use App\Repository\LocaleRepository;
use App\Repository\ProductTypeRepository;
use App\Tests\TestData\Factory\Locale\LocaleFactory;
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
#[UsesClass(LocaleRepository::class)]
#[UsesClass(ProductTypeTitleTranslation::class)]
#[UsesClass(RelatedTimestampableEntity::class)]
#[UsesClass(LocaleListener::class)]
class ProductTypeApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private Client $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

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
        $locale = LocaleFactory::createOne(['id' => 'de_DE']);

        $response = $this->client->request(
            'POST',
            '/api/product_types',
            [
                'body' => '{
                                "title":"Generic Product",
                                "code":"generic_product",
                                "translations": [
                                    {
                                        "locale": "/api/locales/'.$locale->getId().'",
                                        "value": "Generisches Produkt"
                                    }
                                ]
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['title' => 'Generic Product']);
        $this->assertJsonContains(['code' => 'generic_product']);

        $this->assertStringContainsString('"\/api\/product_type_title_translations\/', $response->getContent());
        $this->assertStringContainsString('"createdAt":', $response->getContent());
    }
}
