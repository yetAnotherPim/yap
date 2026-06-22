<?php

declare(strict_types=1);

namespace App\PluginManager\Command;

use App\PluginManager\PluginManager;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'yap:plugins:list', description: 'List available plugins')]
class PluginsListCommand
{
    public function __construct(private PluginManager $pluginManager)
    {
    }

    public function __invoke(
        SymfonyStyle $io,
        OutputInterface $output,
        Application $application,
        #[Option(description: 'Refresh plugin list')]
        bool $refresh = false,
    ): int {
        if (true === $refresh) {
            $application->doRun(new ArrayInput(['cache:clear']), $output);
        }

        $pluginData = [];

        $plugins = $this->pluginManager->getPlugins();

        foreach ($plugins as $plugin) {
            $pluginData[] = [
                $plugin->getName(),
                $plugin->getNamespace(),
                true === $plugin->isActive() ? '<info>✓</info>' : 'x',
                $plugin->getVersion(),
                $plugin->getInstalledVersion(),
                true === $plugin->isUpdatable() ? '<info>✓</info>' : 'x',
            ];
        }

        $io->table(
            ['name', 'namespace', 'active', 'version', 'installed Version', 'updatable'],
            $pluginData
        );

        return Command::SUCCESS;
    }
}
