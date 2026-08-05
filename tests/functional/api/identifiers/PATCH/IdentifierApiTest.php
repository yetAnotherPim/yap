<?php

declare(strict_types=1);

namespace App\Tests\functional\api\identifiers\PATCH;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Doctrine\Event\EventListener\RelatedTimestampUpdater;
use App\Entity\Locale\Locale;
use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use App\Entity\User\User;
use App\Localisation\Event\EventListener\LocaleListener;
use App\Localisation\Event\EventListener\LoginSuccessListener;
use App\Repository\IdentifierRepository;
use App\Repository\LocaleRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Tests\helper\Trait\Api\AuthTrait;
use App\Tests\TestData\Factory\Product\Identifier\IdentifierFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(RelatedTimestampUpdater::class)]
#[CoversClass(Identifier::class)]
#[CoversClass(IdentifierRepository::class)]
#[UsesClass(Product::class)]
#[UsesClass(IdentifierType::class)]
#[UsesClass(ProductType::class)]
#[UsesClass(ProductRepository::class)]
#[UsesClass(LocaleListener::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(User::class)]
#[UsesClass(LoginSuccessListener::class)]
#[UsesClass(UserRepository::class)]
#[UsesClass(Locale::class)]
class IdentifierApiTest extends ApiTestCase
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
        /** @phpstan-var Identifier $identifier */
        $identifier = IdentifierFactory::createOne();

        $response = $this->client->request(
            'GET',
            '/api/products/'.$identifier->getProduct()->getId(),
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
            '/api/identifiers/'.$identifier->getId(),
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
            '/api/products/'.$identifier->getProduct()->getId(),
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
     * @throws TransportExceptionInterface
     */
    public function testRequestRequiresAuth(): void
    {
        $this->client->request('PATCH', '/api/identifiers/1');

        $this->assertResponseStatusCodeSame(401);
    }
}
