<?php

declare(strict_types=1);

namespace App\Tests\functional\api\field_types\POST;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Abstract\RelatedTimestampableEntity;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Field\Type\Translation\FieldTypeTitleTranslation;
use App\Localisation\Event\EventListener\LocaleListener;
use App\Repository\FieldTypeRepository;
use App\Repository\LocaleRepository;
use App\Tests\TestData\Factory\Locale\LocaleFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(FieldType::class)]
#[CoversClass(FieldTypeRepository::class)]
#[UsesClass(Locale::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(FieldTypeTitleTranslation::class)]
#[UsesClass(RelatedTimestampableEntity::class)]
#[UsesClass(LocaleListener::class)]
class FieldTypeApiTest extends ApiTestCase
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
            '/api/field_types',
            [
                'body' => '{
                                "title":"Product name",
                                "code":"product_name",
                                "translatable": true,
                                "filterable": true,
                                "inputType": "string",
                                "translations": [
                                    {
                                        "locale": "/api/locales/'.$locale->getId().'",
                                        "value": "Produktname"
                                    }
                                ]
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['title' => 'Product name']);
        $this->assertJsonContains(['translatable' => true]);
        $this->assertJsonContains(['filterable' => true]);
        $this->assertJsonContains(['inputType' => 'string']);

        $this->assertStringContainsString('"\/api\/field_type_title_translations\/', $response->getContent());
        $this->assertStringContainsString('"createdAt":', $response->getContent());
    }
}
