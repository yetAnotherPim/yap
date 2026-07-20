<?php

declare(strict_types=1);

namespace App\Tests\unit\PluginManager\Command;

use App\Entity\Plugin\Plugin;
use App\PluginManager\Command\PluginsListCommand;
use App\PluginManager\PluginManager;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[CoversClass(PluginsListCommand::class)]
class PluginsListCommandTest extends MockeryTestCase
{
    /**
     * @throws \Throwable
     */
    public function testInvokeRendersPluginTable(): void
    {
        $plugin = $this->createPluginMock(
            name: 'SamplePlugin',
            namespace: 'App\\plugins\\SamplePlugin\\Plugin',
            active: true,
            version: '1.0.0',
            installedVersion: '0.9.0',
            updatable: true,
        );

        $pluginManager = \Mockery::mock(PluginManager::class);
        $pluginManager
            ->shouldReceive('getPlugins')
            ->once()
            ->andReturn(new ArrayCollection([$plugin]));

        $io = \Mockery::mock(SymfonyStyle::class);
        $io
            ->shouldReceive('table')
            ->once()
            ->with(
                ['name', 'namespace', 'active', 'version', 'installed Version', 'updatable'],
                [[
                    'SamplePlugin',
                    'App\\plugins\\SamplePlugin\\Plugin',
                    '<info>✓</info>',
                    '1.0.0',
                    '0.9.0',
                    '<info>✓</info>',
                ]]
            );

        $application = \Mockery::mock(Application::class);
        $application
            ->shouldReceive('doRun')
            ->andReturn(Command::SUCCESS);

        $output = \Mockery::mock(OutputInterface::class);

        $command = new PluginsListCommand($pluginManager);

        $result = $command($io, $output, $application, true);

        $this->assertSame(Command::SUCCESS, $result);
    }

    private function createPluginMock(
        string $name,
        string $namespace,
        bool $active,
        string $version,
        ?string $installedVersion,
        bool $updatable,
    ): Plugin&Mockery\MockInterface {
        $plugin = \Mockery::mock(Plugin::class);
        $plugin->shouldReceive('getName')->andReturn($name);
        $plugin->shouldReceive('getNamespace')->andReturn($namespace);
        $plugin->shouldReceive('isActive')->andReturn($active);
        $plugin->shouldReceive('getVersion')->andReturn($version);
        $plugin->shouldReceive('getInstalledVersion')->andReturn($installedVersion);
        $plugin->shouldReceive('isUpdatable')->andReturn($updatable);

        return $plugin;
    }
}
