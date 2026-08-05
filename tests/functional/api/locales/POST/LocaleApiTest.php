<?php

declare(strict_types=1);

namespace App\Tests\functional\api\locales\POST;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Doctrine\Event\EventListener\RelatedTimestampUpdater;
use App\Entity\Locale\Locale;
use App\Entity\User\User;
use App\Localisation\Event\EventListener\LocaleListener;
use App\Localisation\Event\EventListener\LoginSuccessListener;
use App\Repository\LocaleRepository;
use App\Repository\UserRepository;
use App\Tests\helper\Trait\Api\AuthTrait;
use App\Tests\TestData\Factory\Locale\LocaleFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(RelatedTimestampUpdater::class)]
#[CoversClass(Locale::class)]
#[CoversClass(LocaleRepository::class)]
#[UsesClass(LocaleListener::class)]
#[UsesClass(User::class)]
#[UsesClass(LoginSuccessListener::class)]
#[UsesClass(UserRepository::class)]
class LocaleApiTest extends ApiTestCase
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
        $this->client->request(
            'POST',
            '/api/locales',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
                'body' => '{
                                "id":"fr_FR"
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['id' => 'fr_FR']);
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
        LocaleFactory::findOrCreate(['id' => 'de_DE']);

        $this->client->request(
            'POST',
            '/api/locales',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
                'body' => '{
                                "id":"de_DE"
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(500);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestRequiresAuth(): void
    {
        $this->client->request('POST', '/api/locales');

        $this->assertResponseStatusCodeSame(401);
    }
}
