<?php

declare(strict_types=1);

namespace App\Tests\functional\api\plugin\GET;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Controller\Admin\FieldTypeCrudController;
use App\Controller\Admin\IdentifierCrudController;
use App\Controller\Admin\IdentifierTypeCrudController;
use App\Controller\Admin\LocaleCrudController;
use App\Controller\Admin\ProductCrudController;
use App\Controller\Admin\ProductTypeCrudController;
use App\Controller\Admin\ProductTypeFieldConfigurationCrudController;
use App\Entity\Plugin\Plugin;
use App\Kernel;
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
use App\Repository\IdentifierTypeRepository;
use App\Repository\PluginRepository;
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
class PluginTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private Client $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

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
        $this->client->request('GET', '/api/plugins');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 0]);

        $this->client->request('GET', '/api/refresh/plugins');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);

        $this->client->request('GET', '/api/plugins');
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
    public function testGetPluginsWithInvalidPlugin(): void
    {
        self::getContainer()->set(\App\Tests\TestData\plugins\TestPlugin\Plugin::class, new \stdClass());

        $this->client->request('GET', '/api/refresh/plugins');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 1]);
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

        $this->client->request('GET', '/api/plugins');
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

        $this->client->request('GET', '/api/refresh/plugins');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);
    }
}
