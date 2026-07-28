<?php

declare(strict_types=1);

namespace App\Tests\functional\api\field\PATCH;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use App\Localisation\Event\EventListener\LocaleListener;
use App\Repository\FieldRepository;
use App\Repository\FieldValueTranslationRepository;
use App\Repository\LocaleRepository;
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

#[CoversClass(Field::class)]
#[CoversClass(FieldRepository::class)]
#[UsesClass(Locale::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(FieldValueTranslation::class)]
#[UsesClass(FieldValueTranslationRepository::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(Product::class)]
#[UsesClass(ProductType::class)]
#[UsesClass(LocaleListener::class)]
class FieldApiTest extends ApiTestCase
{
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
            'PATCH',
            '/api/fields/'.$field->getId(),
            [
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
                'body' => '{
                                "value":"123456"
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['value' => '123456']);
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
    public function testPatchWithAddingTranslation(): void
    {
        /** @phpstan-var Field $field */
        $field = FieldFactory::createOne();
        $locale = LocaleFactory::createOne(['id' => 'en_GB']);

        $response = $this->client->request(
            'PATCH',
            '/api/fields/'.$field->getId(),
            [
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
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
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
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
}
