<?php

declare(strict_types=1);

namespace App\Tests\functional\api\plugin\POST;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\Entity\Plugin\Plugin;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Plugin::class)]
class PluginTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private Client $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        parent::setUp();
    }

    public function testPluginActivationAndDeactivation(): void
    {
        $this->client->request('GET', '/api/plugins');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 0]);

        $response = $this->client->request('GET', '/api/refresh/plugins');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 1]);
        $content = $response->getContent();
        $this->assertStringContainsString('"active":false,"version":"0.9.0","updatable":false', $content);
        $this->assertStringNotContainsString('"installedVersion', $content);

        $dumpfileContent = file_get_contents(__DIR__.'/../../../../../var/plugin_configuration/test_plugin_configuration');
        $this->assertEmpty($dumpfileContent);

        $response = $this->client->request('GET', '/api/plugins');
        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains(['totalItems' => 1]);
        $this->assertStringContainsString('"active":false', $response->getContent());
        $this->assertStringNotContainsString('"installedVersion', $content);

        $this->client->request('POST', '/api/plugin/activate/SamplePlugin');
        $this->assertResponseStatusCodeSame(202);
        $this->assertJsonContains(['success' => true]);

        $response = $this->client->request('GET', '/api/plugins');
        $this->assertResponseStatusCodeSame(200);
        $this->assertStringContainsString('"active":true,"installedVersion":"0.9.0"', $response->getContent());

        $dumpfileContent = file_get_contents(__DIR__.'/../../../../../var/plugin_configuration/test_plugin_configuration');
        $this->assertNotEmpty($dumpfileContent);
        $this->assertEquals('SamplePlugin', $dumpfileContent);

        $this->client->request('POST', '/api/plugin/deactivate/SamplePlugin');
        $this->assertResponseStatusCodeSame(202);
        $this->assertJsonContains(['success' => true]);

        $response = $this->client->request('GET', '/api/plugins');
        $this->assertResponseStatusCodeSame(200);
        $this->assertStringContainsString('"active":false,"installedVersion":"0.9.0"', $response->getContent());

        $dumpfileContent = file_get_contents(__DIR__.'/../../../../../var/plugin_configuration/test_plugin_configuration');
        $this->assertEmpty($dumpfileContent);
    }
}
