<?php

declare(strict_types=1);

namespace App\Tests\functional\api\fields\POST;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Doctrine\Event\EventListener\RelatedTimestampUpdater;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\Enum\InputType;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Entity\Product\Type\ProductType;
use App\Entity\User\User;
use App\Localisation\Event\EventListener\LocaleListener;
use App\Localisation\Event\EventListener\LoginSuccessListener;
use App\Repository\FieldRepository;
use App\Repository\FieldTypeRepository;
use App\Repository\LocaleRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Tests\helper\Trait\Api\AuthTrait;
use App\Tests\TestData\Factory\Product\Field\Type\FieldTypeFactory;
use App\Tests\TestData\Factory\Product\ProductFactory;
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
#[CoversClass(InputType::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(Product::class)]
#[UsesClass(ProductRepository::class)]
#[UsesClass(FieldTypeRepository::class)]
#[UsesClass(ProductType::class)]
#[UsesClass(LocaleListener::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(User::class)]
#[UsesClass(LoginSuccessListener::class)]
#[UsesClass(UserRepository::class)]
#[UsesClass(Locale::class)]
class FieldApiTest extends ApiTestCase
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
        /** @phpstan-var FieldType $fieldType */
        $fieldType = FieldTypeFactory::createOne();

        /** @phpstan-var Product $product */
        $product = ProductFactory::createOne();

        $response = $this->client->request(
            'GET',
            '/api/products/'.$product->getId(),
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
            'POST',
            '/api/fields',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
                'body' => '{
                                "value":"123456",
                                "type":"/api/field_types/'.$fieldType->getId().'",
                                "product":"/api/products/'.$product->getId().'"
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains(['value' => '123456']);
        $this->assertStringContainsString('"createdAt":', $response->getContent());
        $this->assertStringNotContainsString('"updatedAt":', $response->getContent());

        /** @phpstan-var string $id */
        $id = $response->toArray()['id'];

        /** test products contains fields */
        $response = $this->client->request(
            'GET',
            '/api/products/'.$product->getId(),
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );

        $this->assertStringContainsString($id, $response->getContent());

        $productData = $response->toArray();
        $this->assertArrayHasKey('updatedAt', $productData);
        $this->assertNotEquals($oldUpdatedAt, $productData['updatedAt'], 'Product updatedAt should have changed');
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestRequiresAuth(): void
    {
        $this->client->request('POST', '/api/fields');

        $this->assertResponseStatusCodeSame(401);
    }
}
