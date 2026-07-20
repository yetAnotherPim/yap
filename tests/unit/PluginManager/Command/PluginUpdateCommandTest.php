<?php

declare(strict_types=1);

namespace App\Tests\unit\PluginManager\Command;

use App\PluginManager\Command\PluginUpdateCommand;
use App\PluginManager\Event\Enum\Action;
use App\PluginManager\PluginManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

#[CoversClass(PluginUpdateCommand::class)]
class PluginUpdateCommandTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    public function testInvoke(): void
    {
        $pluginName = 'SamplePlugin';

        $pluginManager = $this->createMock(PluginManager::class);
        $pluginManager
            ->expects(self::once())
            ->method('performWorkflow')
            ->with(Action::UPDATE, $pluginName)
            ->willReturn(true);

        $io = $this->createMock(SymfonyStyle::class);
        $io
            ->expects(self::never())
            ->method('error');

        $command = new PluginUpdateCommand($pluginManager);

        $result = $command($io, new NullOutput(), $pluginName);

        $this->assertSame(Command::SUCCESS, $result);
    }

    /**
     * @throws \Throwable
     */
    public function testInvokeReturnsFailureWhenPluginCannotBeActivated(): void
    {
        $pluginName = 'SamplePlugin';

        $pluginManager = $this->createMock(PluginManager::class);
        $pluginManager
            ->expects(self::once())
            ->method('performWorkflow')
            ->with(Action::UPDATE, $pluginName)
            ->willReturn(false);

        $io = $this->createMock(SymfonyStyle::class);
        $io
            ->expects(self::once())
            ->method('error')
            ->with('Plugin cannot be updated!');

        $command = new PluginUpdateCommand($pluginManager);

        $result = $command($io, new NullOutput(), $pluginName);

        $this->assertSame(Command::FAILURE, $result);
    }
}
