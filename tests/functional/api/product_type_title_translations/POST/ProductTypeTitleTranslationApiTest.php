<?php

declare(strict_types=1);

namespace App\Tests\functional\api\product_type_title_translations\POST;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Doctrine\Event\EventListener\RelatedTimestampUpdater;
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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(RelatedTimestampUpdater::class)]
#[CoversClass(ProductTypeTitleTranslation::class)]
#[CoversClass(ProductTypeTitleTranslationRepository::class)]
#[UsesClass(Locale::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(ProductType::class)]
#[UsesClass(ProductTypeRepository::class)]
#[UsesClass(LocaleListener::class)]
#[UsesClass(User::class)]
#[UsesClass(LoginSuccessListener::class)]
#[UsesClass(UserRepository::class)]
class ProductTypeTitleTranslationApiTest extends ApiTestCase
{
    use AuthTrait;

    protected static ?bool $alwaysBootKernel = true;

    private Client $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

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
    public function testPost(): void
    {
        $productType = ProductTypeFactory::createOne();
        $locale = LocaleFactory::findOrCreate(['id' => 'en_GB']);

        $response = $this->client->request(
            'POST',
            '/api/product_type_title_translations',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
                'body' => '{
                                "value":"123456",
                                "productType":"/api/product_types/'.$productType->getId().'",
                                "locale":"/api/locales/'.$locale->getId().'"
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['value' => '123456']);
        $this->assertStringContainsString('"createdAt":', $response->getContent());
        $this->assertStringNotContainsString('"updatedAt":', $response->getContent());
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestRequiresAuth(): void
    {
        $this->client->request('POST', '/api/product_type_title_translations');

        $this->assertResponseStatusCodeSame(401);
    }
}
