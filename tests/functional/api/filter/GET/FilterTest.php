<?php

declare(strict_types=1);

namespace App\Tests\functional\api\filter\GET;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Product\Field\Field;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Field\Type\Section\FieldSection;
use App\Entity\Product\Identifier\Identifier;
use App\Entity\Product\Identifier\Type\IdentifierType;
use App\Entity\Product\Product;
use App\Repository\IdentifierRepository;
use App\Repository\ProductRepository;
use App\Tests\TestData\Factory\Product\ProductFactory;
use App\Tests\TestData\Story\MultipleProductsStory;
use App\Tests\TestData\Story\SimpleProductStory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Zenstruck\Foundry\Test\Factories;

#[CoversClass(Product::class)]
#[CoversClass(Identifier::class)]
#[CoversClass(IdentifierRepository::class)]
#[CoversClass(ProductRepository::class)]
#[UsesClass(IdentifierType::class)]
#[UsesClass(Field::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(FieldSection::class)]
class FilterTest extends ApiTestCase
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
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetProductsViaIdentifier(): void
    {
        MultipleProductsStory::load();

        $this->client->request('GET', '/api/filter/identifiers?value=1111&type=Internal');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 1]);

        $this->client->request('GET', '/api/filter/identifiers?value=01111&type=GTIN');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);

        $this->client->request('GET', '/api/filter/identifiers?value[]=1111&value[]=2222&type=Internal');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);

        $jsonSchema = file_get_contents(__DIR__.'/../../../../JsonSchema/intentifier.filter.schema.json');
        $this->assertIsString($jsonSchema);
        $this->assertMatchesJsonSchema($jsonSchema);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetProductsWithoutIdentifier(): void
    {
        SimpleProductStory::load();
        ProductFactory::createOne([
            'identifiers' => [],
        ]);

        $response = $this->client->request('GET', '/api/filter/hasIdentifier?value=1');
        $this->assertJsonContains(['totalItems' => 1]);
        $this->assertStringContainsString('"identifiers":[{"@id":', $response->getContent());
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->request('GET', '/api/filter/hasIdentifier?value=0');

        $this->assertJsonContains(['totalItems' => 1]);
        $this->assertStringContainsString('"identifiers":[]', $response->getContent());
        $this->assertResponseStatusCodeSame(200);
    }
}
