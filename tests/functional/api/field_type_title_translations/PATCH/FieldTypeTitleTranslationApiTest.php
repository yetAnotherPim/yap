<?php

declare(strict_types=1);

namespace App\Tests\functional\api\field_type_title_translations\PATCH;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Controller\Admin\FieldTypeCrudController;
use App\Controller\Admin\IdentifierCrudController;
use App\Controller\Admin\IdentifierTypeCrudController;
use App\Controller\Admin\LocaleCrudController;
use App\Controller\Admin\PluginCrudController;
use App\Controller\Admin\ProductCrudController;
use App\Controller\Admin\ProductTypeCrudController;
use App\Controller\Admin\ProductTypeFieldConfigurationCrudController;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Field\Type\Translation\FieldTypeTitleTranslation;
use App\PluginManager\Cache\CacheClearer;
use App\Repository\FieldTypeRepository;
use App\Repository\FieldTypeTitleTranslationRepository;
use App\Repository\IdentifierTypeRepository;
use App\Repository\LocaleRepository;
use App\Repository\PluginRepository;
use App\Tests\TestData\Factory\Locale\LocaleFactory;
use App\Tests\TestData\Factory\Product\Field\Type\FieldTypeFactory;
use App\Tests\TestData\Factory\Product\Field\Type\Translation\FieldTypeTitleTranslationFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[CoversClass(FieldTypeTitleTranslation::class)]
#[CoversClass(FieldTypeTitleTranslationRepository::class)]
#[UsesClass(Locale::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(FieldType::class)]
#[UsesClass(FieldTypeRepository::class)]
// Deleting the cache is causing instantiation of following classes
#[UsesClass(CacheClearer::class)]
#[UsesClass(FieldTypeCrudController::class)]
#[UsesClass(IdentifierCrudController::class)]
#[UsesClass(IdentifierTypeCrudController::class)]
#[UsesClass(IdentifierTypeRepository::class)]
#[UsesClass(LocaleCrudController::class)]
#[UsesClass(ProductTypeCrudController::class)]
#[UsesClass(ProductCrudController::class)]
#[UsesClass(ProductTypeFieldConfigurationCrudController::class)]
#[UsesClass(PluginCrudController::class)]
#[UsesClass(PluginRepository::class)]
class FieldTypeTitleTranslationApiTest extends ApiTestCase
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
        $fieldType = FieldTypeFactory::createOne();
        $locale = LocaleFactory::createOne(['id' => 'en_GB']);

        $translation = FieldTypeTitleTranslationFactory::createOne([
            'locale' => $locale,
            'fieldType' => $fieldType,
            'value' => 'Something',
        ]);

        $response = $this->client->request(
            'PATCH',
            '/api/field_type_title_translations/'.$translation->getId(),
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
    }
}
