<?php

declare(strict_types=1);

namespace App\Tests\functional\api\plugin\GET;

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
use App\Controller\Provider\DashboardStatisticsProvider;
use App\Controller\Provider\DashboardSystemStatusProvider;
use App\Doctrine\Event\EventListener\RelatedTimestampUpdater;
use App\Entity\Locale\Locale;
use App\Entity\Plugin\Plugin;
use App\Entity\User\User;
use App\Form\Product\Fields\Composer\ProductFieldsComposer;
use App\Form\Product\Fields\Composer\Translation\ProductFieldTranslationComposer;
use App\Form\Product\Fields\Renderer\ProductFieldsRenderer;
use App\Form\Product\Fields\Renderer\Registry\RendererRegistry;
use App\Form\Product\Fields\Synchronizer\FieldSynchronizer;
use App\Form\Product\Fields\Synchronizer\Translation\FieldTranslationSynchronizer;
use App\Form\Title\Translation\Renderer\TitleTranslationFieldRenderer;
use App\Form\Title\Translation\Synchronizer\TitleTranslationSynchronizer;
use App\Kernel;
use App\Localisation\Event\EventListener\LocaleListener;
use App\Localisation\Event\EventListener\LoginSuccessListener;
use App\PluginManager\Cache\CacheClearer;
use App\PluginManager\Cache\Decorator\CacheClearerDecorator;
use App\PluginManager\DependencyInjection\CompilerPass\PluginLoader;
use App\PluginManager\PluginManager;
use App\PluginManager\State\PluginsRefreshProvider;
use App\plugins\SamplePlugin\Command\SampleCommand;
use App\plugins\SamplePlugin\Controller\SampleController;
use App\plugins\SamplePlugin\Decoration\ServiceDecoration;
use App\plugins\SamplePlugin\Entity\SampleEntity;
use App\plugins\SamplePlugin\Repository\SampleRepository;
use App\Repository\FieldTypeRepository;
use App\Repository\IdentifierTypeRepository;
use App\Repository\LocaleRepository;
use App\Repository\PluginRepository;
use App\Repository\ProductRepository;
use App\Repository\ProductTypeRepository;
use App\Repository\UserRepository;
use App\Tests\helper\Trait\Api\AuthTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Zenstruck\Foundry\Attribute\ResetDatabase;

#[CoversClass(RelatedTimestampUpdater::class)]
#[CoversClass(Plugin::class)]
#[CoversClass(PluginRepository::class)]
#[CoversClass(PluginManager::class)]
#[CoversClass(PluginLoader::class)]
#[CoversClass(PluginsRefreshProvider::class)]
#[CoversClass(CacheClearer::class)]
#[CoversClass(CacheClearerDecorator::class)]
#[CoversClass(\App\plugins\SamplePlugin\Plugin::class)]
#[CoversMethod(Kernel::class, 'build')]
// Deleting the cache is causing instantiation of following classes
#[UsesClass(FieldTypeCrudController::class)]
#[UsesClass(IdentifierCrudController::class)]
#[UsesClass(IdentifierTypeCrudController::class)]
#[UsesClass(IdentifierTypeRepository::class)]
#[UsesClass(LocaleCrudController::class)]
#[UsesClass(ProductTypeCrudController::class)]
#[UsesClass(ProductCrudController::class)]
#[UsesClass(ProductTypeFieldConfigurationCrudController::class)]
#[UsesClass(PluginCrudController::class)]
#[UsesClass(UserCrudController::class)]
#[UsesClass(LocaleListener::class)]
#[UsesClass(LocaleRepository::class)]
#[UsesClass(IndexController::class)]
#[UsesClass(User::class)]
#[UsesClass(LoginSuccessListener::class)]
#[UsesClass(UserRepository::class)]
#[UsesClass(Locale::class)]
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
#[UsesClass(FieldTypeRepository::class)]
#[ResetDatabase]
class PluginTest extends ApiTestCase
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
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetPlugins(): void
    {
        $this->client->request(
            'GET',
            '/api/plugins',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 0]);

        $this->client->request(
            'GET',
            '/api/refresh/plugins',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);

        $this->client->request(
            'GET',
            '/api/plugins',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);
    }

    /**
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testRefreshPluginsWithMissingDumpedConfigurationFile(): void
    {
        new Filesystem()->remove(__DIR__.'/../../../../../var/cache/test');
        unlink(__DIR__.'/../../../../../var/plugin_configuration/test_plugin_configuration');

        $this->client->request(
            'GET',
            '/api/plugins',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 0]);
    }

    /**
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testRefreshPluginsWithExistingDumpedConfigurationFile(): void
    {
        new Filesystem()->remove(__DIR__.'/../../../../../var/cache/test');
        file_put_contents(
            __DIR__.'/../../../../../var/plugin_configuration/test_plugin_configuration',
            implode(PHP_EOL, ['SamplePlugin', 'TestPlugin'])
        );

        self::getContainer()->reset();
        $kernel = self::$kernel;

        $this->assertInstanceOf(KernelInterface::class, $kernel);
        $kernel->shutdown();
        $kernel->boot();

        $container = self::getContainer();

        $this->assertArrayHasKey(SampleCommand::class, $container->getRemovedIds());
        $this->assertArrayHasKey(SampleController::class, $container->getRemovedIds());
        $this->assertArrayHasKey(ServiceDecoration::class, $container->getRemovedIds());
        $this->assertArrayHasKey(SampleEntity::class, $container->getRemovedIds());
        $this->assertArrayHasKey(SampleRepository::class, $container->getRemovedIds());

        $this->client->request(
            'GET',
            '/api/refresh/plugins',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->authToken,
                ],
            ]
        );
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function testRequestRequiresAuth(): void
    {
        $this->client->request('GET', '/api/plugins');

        $this->assertResponseStatusCodeSame(401);
    }
}
