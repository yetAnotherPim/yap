<?php

declare(strict_types=1);

namespace App\Tests\functional\api\product_type_title_translations\PATCH;

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
use App\Tests\TestData\Factory\Product\Type\Translation\ProductTypeTitleTranslationFactory;
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
    public function testPatch(): void
    {
        $productType = ProductTypeFactory::createOne();
        $locale = LocaleFactory::findOrCreate(['id' => 'en_GB']);

        $translation = ProductTypeTitleTranslationFactory::createOne([
            'locale' => $locale,
            'productType' => $productType,
            'value' => 'Something',
        ]);

        $response = $this->client->request(
            'PATCH',
            '/api/product_type_title_translations/'.$translation->getId(),
            [
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
                'body' => '{
                                "value":"Something else"
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['value' => 'Something else']);
        $this->assertStringContainsString('"createdAt":', $response->getContent());
        $this->assertStringContainsString('"updatedAt":', $response->getContent());
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestRequiresAuth(): void
    {
        $this->client->request('PATCH', '/api/product_type_title_translations/1');

        $this->assertResponseStatusCodeSame(401);
    }
}
