<?php

declare(strict_types=1);

namespace App\Tests\functional\api\locales\POST;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Locale\Locale;
use App\Repository\LocaleRepository;
use App\Tests\TestData\Factory\Locale\LocaleFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Zenstruck\Foundry\Test\Factories;

#[CoversClass(Locale::class)]
#[CoversClass(LocaleRepository::class)]
class LocaleApiTest extends ApiTestCase
{
    use Factories;
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
        $this->client->request(
            'POST',
            '/api/locales',
            [
                'body' => '{
                                "id":"de_DE"
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['id' => 'de_DE']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testPostDuplicate(): void
    {
        LocaleFactory::createOne(['id' => 'de_DE']);

        $this->client->request(
            'POST',
            '/api/locales',
            [
                'body' => '{
                                "id":"de_DE"
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(500);
    }
}
