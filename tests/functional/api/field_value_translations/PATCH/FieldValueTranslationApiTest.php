<?php

declare(strict_types=1);

namespace App\Tests\functional\api\field_value_translation\PATCH;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Translation\FieldValueTranslation;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Field\Type\Section\FieldSection;
use App\Entity\Product\Product;
use App\Repository\FieldRepository;
use App\Repository\FieldTypeRepository;
use App\Repository\FieldValueTranslationRepository;
use App\Repository\LocaleRepository;
use App\Repository\ProductRepository;
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

#[CoversClass(FieldValueTranslation::class)]
#[CoversClass(FieldValueTranslationRepository::class)]
#[UsesClass(Field::class)]
#[UsesClass(FieldRepository::class)]
#[UsesClass(Locale::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(FieldSection::class)]
#[UsesClass(Product::class)]
#[UsesClass(ProductRepository::class)]
#[UsesClass(FieldTypeRepository::class)]
class FieldValueTranslationApiTest extends ApiTestCase
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
    public function testPatch(): void
    {
        $field = FieldFactory::createOne();
        $locale = LocaleFactory::createOne(['id' => 'en_GB']);

        $translation = FieldValueTranslationFactory::createOne([
            'locale' => $locale,
            'field' => $field,
            'value' => 'Something',
        ]);

        $product = $field->getProduct();
        $updatedAt = $product->getUpdatedAt();

        $response = $this->client->request(
            'PATCH',
            '/api/field_value_translations/'.$translation->getId(),
            [
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
                'body' => '{
                                "value":"Something else"
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['value' => 'Something else']);
        $this->assertStringContainsString('"createdAt":', $response->getContent());
        $this->assertStringContainsString('"updatedAt":', $response->getContent());

        /** @phpstan-var string $id */
        $id = $response->toArray()['id'];

        /** test products contains fields */
        $response = $this->client->request('GET', '/api/products/'.$field->getProduct()->getId());
        $this->assertStringContainsString($id, $response->getContent());
        $this->assertStringContainsString('"locale":"\/api\/locales\/en_GB","value":"Something else"', $response->getContent());

        $this->assertNotEquals($updatedAt, $product->getUpdatedAt());
    }
}
