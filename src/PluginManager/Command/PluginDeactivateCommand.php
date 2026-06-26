<?php

declare(strict_types=1);

namespace App\PluginManager\Command;

use App\PluginManager\Event\Enum\Action;
use App\PluginManager\PluginManager;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'yap:plugin:deactivate', description: 'Deactivate a plugin')]
class PluginDeactivateCommand
{
    public function __construct(private readonly PluginManager $pluginManager)
    {
    }

    /**
     * @throws \Throwable
     */
    public function __invoke(
        SymfonyStyle $io,
        OutputInterface $output,
        #[Argument(description: 'Plugin name')]
        string $pluginName,
    ): int {
        $result = $this->pluginManager->performWorkflow(Action::DEACTIVATE, $pluginName);

        if (false === $result) {
            $io->error('Plugin cannot be deactivated!');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
