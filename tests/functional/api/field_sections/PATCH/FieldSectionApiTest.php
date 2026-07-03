<?php

declare(strict_types=1);

namespace App\Tests\functional\api\field_sections\PATCH;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Field\Type\Section\FieldSection;
use App\Repository\FieldSectionRepository;
use App\Repository\FieldTypeRepository;
use App\Tests\TestData\Factory\Product\Field\Type\FieldTypeFactory;
use App\Tests\TestData\Factory\Product\Field\Type\Section\FieldSectionFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(FieldSection::class)]
#[CoversClass(FieldSectionRepository::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(FieldTypeRepository::class)]
class FieldSectionApiTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private Client $client;

    private FieldTypeRepository $fieldTypeRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = $this->getContainer();

        $fieldTypeRepository = $container->get(FieldTypeRepository::class);
        $this->assertInstanceOf(FieldTypeRepository::class, $fieldTypeRepository);

        $this->fieldTypeRepository = $fieldTypeRepository;

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
        $fieldSection = FieldSectionFactory::createOne(['title' => 'Something']);

        $response = $this->client->request(
            'PATCH',
            '/api/field_sections/'.$fieldSection->getId(),
            [
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
                'body' => '{
                                "title":"Product name"
                            }',
            ]
        );

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['title' => 'Product name']);
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
    public function testPatchRemovingFieldType(): void
    {
        $fieldSection = FieldSectionFactory::createOne(
            [
                'title' => 'Something',
                'fieldTypes' => [
                    FieldTypeFactory::new(),
                    FieldTypeFactory::new(),
                ],
            ]
        );

        $this->assertCount(2, $this->fieldTypeRepository->findAll());

        $response = $this->client->request(
            'PATCH',
            '/api/field_sections/'.$fieldSection->getId(),
            [
                'headers' => ['Content-Type' => 'application/merge-patch+json'],
                'body' => '{
                                "title":"Product name",
                                "fieldTypes":[]
                            }',
            ]
        );

        // unassigned fieldTypes won't get removed
        $fieldTypes = $this->fieldTypeRepository->findBy(['section' => null]);

        $this->assertCount(2, $fieldTypes);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['title' => 'Product name']);
        $this->assertStringContainsString('"createdAt":', $response->getContent());
        $this->assertStringContainsString('"updatedAt":', $response->getContent());
    }
}
