<?php

declare(strict_types=1);

namespace App\Tests\functional\api\locales\GET;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Locale\Locale;
use App\Tests\TestData\Factory\Locale\LocaleFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Zenstruck\Foundry\Test\Factories;

#[CoversClass(Locale::class)]
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
    public function testGet(): void
    {
        LocaleFactory::createOne(['id' => 'de_DE']);

        $this->client->request(
            'GET',
            '/api/locales/de_DE'
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['id' => 'de_DE']);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCollection(): void
    {
        LocaleFactory::createOne(['id' => 'de_DE']);
        LocaleFactory::createOne(['id' => 'en_GB']);

        $this->client->request(
            'GET',
            '/api/locales'
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);
    }
}
