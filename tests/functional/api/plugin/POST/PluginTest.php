<?php

declare(strict_types=1);

namespace App\Tests\functional\api\plugin\POST;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Controller\Api\PluginWorkflowController;
use App\Entity\Plugin\Plugin;
use App\Kernel;
use App\PluginManager\Cache\CacheClearer;
use App\PluginManager\Cache\Decorator\CacheClearerDecorator;
use App\PluginManager\DependencyInjection\CompilerPass\PluginLoader;
use App\PluginManager\Event\Enum\Action;
use App\PluginManager\Event\WorkflowEvent\EventListener\PluginWorkflowEventListener;
use App\PluginManager\Event\WorkflowEvent\Exception\PluginWorkflowException;
use App\PluginManager\Event\WorkflowEvent\PluginActivateEvent;
use App\PluginManager\Event\WorkflowEvent\PluginDeactivateEvent;
use App\PluginManager\Event\WorkflowEvent\PluginUpdateEvent;
use App\PluginManager\PluginManager;
use App\PluginManager\State\PluginsRefreshProvider;
use App\Repository\PluginRepository;
use App\Tests\helper\Trait\DatabaseTrait;
use App\Tests\TestData\Factory\Plugin\PluginFactory;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Zenstruck\Foundry\Test\Factories;

#[CoversClass(Plugin::class)]
#[CoversClass(PluginWorkflowEventListener::class)]
#[CoversClass(PluginWorkflowController::class)]
#[CoversClass(PluginManager::class)]
#[CoversClass(PluginUpdateEvent::class)]
#[CoversClass(PluginActivateEvent::class)]
#[CoversClass(PluginDeactivateEvent::class)]
#[CoversClass(PluginWorkflowException::class)]
#[CoversClass(CacheClearer::class)]
#[CoversClass(CacheClearerDecorator::class)]
#[CoversClass(PluginsRefreshProvider::class)]
#[CoversClass(PluginRepository::class)]
#[CoversClass(Action::class)]
#[CoversClass(\App\plugins\SamplePlugin\Plugin::class)]
#[CoversMethod(Kernel::class, 'build')]
#[CoversClass(PluginLoader::class)]
class PluginTest extends ApiTestCase
{
    use DatabaseTrait;
    use Factories;

    protected static ?bool $alwaysBootKernel = true;

    private Client $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->assertInstanceOf(EntityManagerInterface::class, $entityManager);

        $this->entityManager = $entityManager;

        parent::setUp();
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function testPluginActivationAndDeactivation(): void
    {
        $this->assertFalse($this->tableExists('sample_plugin'));

        $this->refreshPlugins();

        $dumpfileContent = file_get_contents(__DIR__.'/../../../../../var/plugin_configuration/test_plugin_configuration');
        $this->assertEmpty($dumpfileContent);

        $this->activatePlugin();

        $this->assertTrue($this->tableExists('sample_plugin'));
        $dumpfileContent = file_get_contents(__DIR__.'/../../../../../var/plugin_configuration/test_plugin_configuration');
        $this->assertNotEmpty($dumpfileContent);
        $this->assertEquals('SamplePlugin', $dumpfileContent);

        $this->deactivatePlugin();

        $dumpfileContent = file_get_contents(__DIR__.'/../../../../../var/plugin_configuration/test_plugin_configuration');
        $this->assertEmpty($dumpfileContent);
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function testPluginActivationWithMissingEntity(): void
    {
        $this->client->request('POST', '/api/plugin/activate/SamplePlugin');
        $this->assertResponseStatusCodeSame(412);
        $this->assertJsonContains(['success' => false, 'errorMessage' => 'Plugin invalid or not found']);
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function testPluginOrphanDeletion(): void
    {
        PluginFactory::createOne([
            'name' => 'DeletedPlugin',
            'installedVersion' => '0.8.0',
            'namespace' => '\\App\\plugins\\DeletedPlugin\\Plugin',
            'active' => true,
        ]);

        $this->client->request('GET', '/api/plugins');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 1]);

        $this->refreshPlugins();

        $this->client->request('GET', '/api/plugins');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);

        $dumpfileContent = file_get_contents(__DIR__.'/../../../../../var/plugin_configuration/test_plugin_configuration');
        $this->assertEmpty($dumpfileContent);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testPluginRefreshWillReturnCorrectValueForIsUpdatable(): void
    {
        PluginFactory::createOne([
            'name' => 'SamplePlugin',
            'installedVersion' => '0.8.0',
            'namespace' => \App\plugins\SamplePlugin\Plugin::class,
            'active' => false,
        ]);

        $response = $this->client->request('GET', '/api/refresh/plugins');
        $this->assertResponseStatusCodeSame(200);

        $content = $response->getContent();
        $this->assertStringContainsString('"active":false,"installedVersion":"0.8.0","version":"0.9.0","updatable":true', $content);
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function testPluginUpdate(): void
    {
        $this->refreshPlugins();
        $this->activatePlugin();
        $this->updatePlugin();

        $this->assertFalse($this->columnExists('sample_plugin', 'new_column'));

        $repository = $this->entityManager->getRepository(Plugin::class);
        $plugin = $repository->findOneBy(['name' => 'SamplePlugin']);

        $this->assertInstanceOf(Plugin::class, $plugin);

        $plugin->setInstalledVersion('0.8.0');

        $this->entityManager->persist($plugin);
        $this->entityManager->flush();

        $this->updatePlugin();

        $this->assertTrue($this->columnExists('sample_plugin', 'new_column'));
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testInvalidAction(): void
    {
        $this->client->request('POST', '/api/plugin/something/SamplePlugin');
        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['success' => false, 'errorMessage' => 'Invalid action given']);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testInvalidPlugin(): void
    {
        $this->client->request('POST', '/api/plugin/activate/Test');
        $this->assertResponseStatusCodeSame(412);
        $this->assertJsonContains(['success' => false, 'errorMessage' => 'Plugin invalid or not found']);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testUpdatingPluginThatHasNotBeenInstalled(): void
    {
        $this->refreshPlugins();

        $this->client->request('POST', '/api/plugin/update/SamplePlugin');
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['success' => false, 'errorMessage' => 'Plugin is not installed']);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testPluginActionsWillCauseWorkflowException(): void
    {
        $this->refreshPlugins();

        $this->client->request('POST', '/api/plugin/activate/TestPlugin');
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['success' => false, 'errorMessage' => 'Oops']);

        $this->client->request('POST', '/api/plugin/deactivate/TestPlugin');
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['success' => false, 'errorMessage' => 'Oops']);

        $repository = $this->entityManager->getRepository(Plugin::class);
        $plugin = $repository->findOneBy(['name' => 'TestPlugin']);

        $this->assertInstanceOf(Plugin::class, $plugin);

        $plugin->setInstalledVersion('0.8.0');

        $this->entityManager->persist($plugin);
        $this->entityManager->flush();

        $this->client->request('POST', '/api/plugin/update/TestPlugin');
        $this->assertResponseStatusCodeSame(422);
        $this->assertJsonContains(['success' => false, 'errorMessage' => 'Oops']);
    }

    /**
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function refreshPlugins(): void
    {
        $this->client->request('GET', '/api/plugins');
        $this->assertResponseStatusCodeSame(200);

        $response = $this->client->request('GET', '/api/refresh/plugins');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);
        $content = $response->getContent();
        $this->assertStringContainsString('"active":false,"version":"0.9.0","updatable":false', $content);
        $this->assertStringNotContainsString('"installedVersion', $content);

        $response = $this->client->request('GET', '/api/plugins');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 2]);
        $this->assertStringContainsString('"active":false', $response->getContent());
        $this->assertStringNotContainsString('"installedVersion', $content);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws Exception
     */
    private function activatePlugin(): void
    {
        $this->client->request('POST', '/api/plugin/activate/SamplePlugin');
        $this->assertResponseStatusCodeSame(202);
        $this->assertJsonContains(['success' => true]);

        $response = $this->client->request('GET', '/api/plugins');
        $this->assertResponseStatusCodeSame(200);
        $this->assertStringContainsString('"active":true,"installedVersion":"0.9.0"', $response->getContent());
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws Exception
     */
    private function deactivatePlugin(): void
    {
        $this->client->request('POST', '/api/plugin/deactivate/SamplePlugin');
        $this->assertResponseStatusCodeSame(202);
        $this->assertJsonContains(['success' => true]);

        $response = $this->client->request('GET', '/api/plugins');
        $this->assertResponseStatusCodeSame(200);
        $this->assertStringContainsString('"active":false,"installedVersion":"0.9.0"', $response->getContent());
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function updatePlugin(): void
    {
        $this->client->request('POST', '/api/plugin/update/SamplePlugin');
        $this->assertResponseStatusCodeSame(202);
        $this->assertJsonContains(['success' => true]);
    }
}
