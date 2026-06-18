<?php

declare(strict_types=1);

namespace App\Tests\functional\api\field\POST;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Product;
use App\Tests\TestData\Factory\Product\Field\Type\FieldTypeFactory;
use App\Tests\TestData\Factory\Product\ProductFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(Field::class)]
class FieldApiTest extends ApiTestCase
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
        /** @phpstan-var FieldType $fieldType */
        $fieldType = FieldTypeFactory::createOne();

        /** @phpstan-var Product $product */
        $product = ProductFactory::createOne();

        $response = $this->client->request(
            'POST',
            '/api/fields',
            [
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
        $response = $this->client->request('GET', '/api/products/'.$product->getId());
        $this->assertStringContainsString($id, $response->getContent());
    }
}
