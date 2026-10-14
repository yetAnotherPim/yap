<?php

declare(strict_types=1);

namespace App\Tests\functional\api\field_type_title_translations\PATCH;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Controller\Admin\IndexController;
use App\Controller\Admin\Products\IdentifierCrudController;
use App\Controller\Admin\Products\ProductCrudController;
use App\Controller\Admin\Settings\FieldTypeCrudController;
use App\Controller\Admin\Settings\IdentifierTypeCrudController;
use App\Controller\Admin\Settings\LocaleCrudController;
use App\Controller\Admin\Settings\PluginCrudController;
use App\Controller\Admin\Settings\ProductTypeCrudController;
use App\Controller\Admin\Settings\ProductTypeFieldConfigurationCrudController;
use App\Controller\Admin\Settings\UserCrudController;
use App\Controller\Admin\User\AccountCrudController;
use App\Controller\Provider\DashboardStatisticsProvider;
use App\Controller\Provider\DashboardSystemStatusProvider;
use App\Doctrine\Event\EventListener\RelatedTimestampUpdater;
use App\Entity\Locale\Locale;
use App\Entity\Product\Field\Type\FieldType;
use App\Entity\Product\Field\Type\Translation\FieldTypeTitleTranslation;
use App\Entity\User\User;
use App\Form\Product\Columns\Composer\ProductColumnsComposer;
use App\Form\Product\Fields\Composer\ProductFieldsComposer;
use App\Form\Product\Fields\Composer\Translation\ProductFieldTranslationComposer;
use App\Form\Product\Fields\Renderer\ProductFieldsRenderer;
use App\Form\Product\Fields\Renderer\Registry\RendererRegistry;
use App\Form\Product\Fields\Synchronizer\FieldSynchronizer;
use App\Form\Product\Fields\Synchronizer\Translation\FieldTranslationSynchronizer;
use App\Form\Product\Filter\Composer\ProductFilterComposer;
use App\Form\Product\Filter\Element\Field\Registry\FieldFilterHandlerRegistry;
use App\Form\Product\Identifier\IdentifierManager;
use App\Form\ProductType\DefaultTranslation\Synchronizer\DefaultTranslationSynchronizer;
use App\Form\Title\Translation\Renderer\TitleTranslationFieldRenderer;
use App\Form\Title\Translation\Synchronizer\TitleTranslationSynchronizer;
use App\Form\User\PasswordFormConfigurator;
use App\Localisation\Event\EventListener\LocaleListener;
use App\Localisation\Event\EventListener\LoginSuccessListener;
use App\PluginManager\Cache\CacheClearer;
use App\PluginManager\PluginManager;
use App\Repository\FieldTypeRepository;
use App\Repository\FieldTypeTitleTranslationRepository;
use App\Repository\IdentifierTypeRepository;
use App\Repository\LocaleRepository;
use App\Repository\PluginRepository;
use App\Repository\ProductColumnConfigurationRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductTypeRepository;
use App\Repository\UserRepository;
use App\Tests\helper\Trait\Api\AuthTrait;
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
use Zenstruck\Foundry\Attribute\ResetDatabase;

#[CoversClass(RelatedTimestampUpdater::class)]
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
#[UsesClass(IndexController::class)]
#[UsesClass(PluginCrudController::class)]
#[UsesClass(PluginRepository::class)]
#[UsesClass(PluginManager::class)]
#[UsesClass(UserCrudController::class)]
#[UsesClass(LocaleListener::class)]
#[UsesClass(User::class)]
#[UsesClass(LoginSuccessListener::class)]
#[UsesClass(UserRepository::class)]
#[UsesClass(ProductFieldsComposer::class)]
#[UsesClass(RendererRegistry::class)]
#[UsesClass(FieldSynchronizer::class)]
#[UsesClass(ProductFieldTranslationComposer::class)]
#[UsesClass(TitleTranslationFieldRenderer::class)]
#[UsesClass(TitleTranslationSynchronizer::class)]
#[UsesClass(DashboardSystemStatusProvider::class)]
#[UsesClass(DashboardStatisticsProvider::class)]
#[UsesClass(ProductFieldsRenderer::class)]
#[UsesClass(FieldTranslationSynchronizer::class)]
#[UsesClass(ProductRepository::class)]
#[UsesClass(ProductTypeRepository::class)]
#[UsesClass(DefaultTranslationSynchronizer::class)]
#[UsesClass(ProductColumnsComposer::class)]
#[UsesClass(ProductFilterComposer::class)]
#[UsesClass(IdentifierManager::class)]
#[UsesClass(FieldFilterHandlerRegistry::class)]
#[UsesClass(ProductColumnConfigurationRepository::class)]
#[UsesClass(AccountCrudController::class)]
#[UsesClass(PasswordFormConfigurator::class)]
#[ResetDatabase]
class FieldTypeTitleTranslationApiTest extends ApiTestCase
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
        $fieldType = FieldTypeFactory::createOne();
        $locale = LocaleFactory::findOrCreate(['id' => 'en_GB']);

        $translation = FieldTypeTitleTranslationFactory::createOne([
            'locale' => $locale,
            'fieldType' => $fieldType,
            'value' => 'Something',
        ]);

        $response = $this->client->request(
            'PATCH',
            '/api/field_type_title_translations/'.$translation->getId(),
            [
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
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

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestRequiresAuth(): void
    {
        $this->client->request('PATCH', '/api/field_type_title_translations/1');

        $this->assertResponseStatusCodeSame(401);
    }
}
