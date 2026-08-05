<?php

declare(strict_types=1);

namespace App\Tests\functional\api\fields\PATCH;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Doctrine\Event\EventListener\RelatedTimestampUpdater;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use App\Entity\User\User;
use App\Localisation\Event\EventListener\LocaleListener;
use App\Localisation\Event\EventListener\LoginSuccessListener;
use App\Repository\FieldRepository;
use App\Repository\FieldValueTranslationRepository;
use App\Repository\LocaleRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Tests\helper\Trait\Api\AuthTrait;
use App\Tests\TestData\Factory\Locale\LocaleFactory;
use App\Tests\TestData\Factory\Product\Field\FieldFactory;
use App\Tests\TestData\Factory\Product\Field\Translation\FieldValueTranslationFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(RelatedTimestampUpdater::class)]
#[CoversClass(Field::class)]
#[CoversClass(FieldRepository::class)]
#[UsesClass(Locale::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(FieldValueTranslation::class)]
#[UsesClass(FieldValueTranslationRepository::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(Product::class)]
#[UsesClass(ProductRepository::class)]
#[UsesClass(ProductType::class)]
#[UsesClass(LocaleListener::class)]
#[UsesClass(User::class)]
#[UsesClass(LoginSuccessListener::class)]
#[UsesClass(UserRepository::class)]
class FieldApiTest extends ApiTestCase
{
    use AuthTrait;

    protected static ?bool $alwaysBootKernel = true;

    private Client $client;

    private FieldValueTranslationRepository $fieldValueTranslationRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = $this->getContainer();

        $fieldValueTranslationRepository = $container->get(FieldValueTranslationRepository::class);
        $this->assertInstanceOf(FieldValueTranslationRepository::class, $fieldValueTranslationRepository);

        $this->fieldValueTranslationRepository = $fieldValueTranslationRepository;

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
        /** @phpstan-var Field $field */
        $field = FieldFactory::createOne();

        $response = $this->client->request(
            'GET',
            '/api/products/'.$field->getProduct()->getId(),
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $productData = $response->toArray();
        $oldUpdatedAt = $productData['updatedAt'] ?? null;

        // Wait a moment to ensure system clock moves forward
        sleep(1);

        $response = $this->client->request(
            'PATCH',
            '/api/fields/'.$field->getId(),
            [
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
                'body' => '{
                                "value":"123456"
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['value' => '123456']);
        $this->assertStringContainsString('"createdAt":', $response->getContent());
        $this->assertStringContainsString('"updatedAt":', $response->getContent());

        $response = $this->client->request(
            'GET',
            '/api/products/'.$field->getProduct()->getId(),
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $productData = $response->toArray();
        $this->assertArrayHasKey('updatedAt', $productData);
        $this->assertNotEquals($oldUpdatedAt, $productData['updatedAt'], 'Product updatedAt should have changed');
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testPatchWithAddingTranslation(): void
    {
        /** @phpstan-var Field $field */
        $field = FieldFactory::createOne();
        $locale = LocaleFactory::findOrCreate(['id' => 'en_GB']);

        $response = $this->client->request(
            'PATCH',
            '/api/fields/'.$field->getId(),
            [
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
                'body' => '{
                                "value":"Katalog",
                                "translations": [
                                    {
                                        "locale":"/api/locales/'.$locale->getId().'",
                                        "value":"Catalogue"
                                    }
                                ]
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['value' => 'Katalog']);
        $this->assertStringContainsString('"locale":"\/api\/locales\/en_GB","value":"Catalogue"', $response->getContent());
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
    public function testPatchWithRemovingTranslationTranslation(): void
    {
        /** @phpstan-var Field $field */
        $field = FieldFactory::createOne([
            'translations' => [
                FieldValueTranslationFactory::createOne(),
            ],
        ]);

        $this->assertCount(1, $this->fieldValueTranslationRepository->findAll());

        $response = $this->client->request(
            'PATCH',
            '/api/fields/'.$field->getId(),
            [
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
                'body' => '{
                                "value":"Katalog",
                                "translations": [
                                ]
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['value' => 'Katalog']);
        $this->assertStringContainsString('"createdAt":', $response->getContent());
        $this->assertStringContainsString('"updatedAt":', $response->getContent());
        $this->assertStringNotContainsString('"locale":"\/api\/locales\/en_GB","value":"Catalogue"', $response->getContent());

        $this->assertCount(0, $this->fieldValueTranslationRepository->findAll());
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestRequiresAuth(): void
    {
        $this->client->request('PATCH', '/api/fields/1');

        $this->assertResponseStatusCodeSame(401);
    }
}
