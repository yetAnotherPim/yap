<?php

declare(strict_types=1);

namespace App\Tests\unit\PluginManager\Cache;

use App\Kernel;
use App\PluginManager\Cache\CacheClearer;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

#[CoversClass(CacheClearer::class)]
class CacheClearerTest extends MockeryTestCase
{
    public function testClearBootsKernelAndRunsCacheClearCommand(): void
    {
        $cacheClearWasRun = false;

        $cacheClearCommand = new Command('cache:clear');
        $cacheClearCommand->setCode(static function () use (&$cacheClearWasRun): int {
            $cacheClearWasRun = true;

            return Command::SUCCESS;
        });

        $commandLoader = new FactoryCommandLoader([
            'cache:clear' => static fn (): Command => $cacheClearCommand,
        ]);

        $container = \Mockery::mock(ContainerInterface::class);
        $container
            ->shouldReceive('has')
            ->with('console.command_loader')
            ->andReturnTrue();
        $container
            ->shouldReceive('get')
            ->with('console.command_loader')
            ->andReturn($commandLoader);
        $container
            ->shouldReceive('get')
            ->with('event_dispatcher')
            ->andReturn(new EventDispatcher());
        $container
            ->shouldReceive('has')
            ->with('console.argument_resolver')
            ->andReturnFalse();
        $container
            ->shouldReceive('hasParameter')
            ->with('console.command.ids')
            ->andReturnFalse();

        $kernel = \Mockery::mock(Kernel::class);
        $kernel
            ->shouldReceive('getEnvironment')
            ->andReturn('test');
        $kernel
            ->shouldReceive('boot');
        $kernel
            ->shouldReceive('getContainer')
            ->andReturn($container);
        $kernel
            ->shouldReceive('getBundles')
            ->andReturn([]);

        new CacheClearer($kernel)->clear();

        $this->assertTrue($cacheClearWasRun);
    }
}
