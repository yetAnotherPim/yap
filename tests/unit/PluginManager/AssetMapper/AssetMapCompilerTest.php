<?php

declare(strict_types=1);

namespace App\Tests\unit\PluginManager\AssetMapper;

use App\Kernel;
use App\PluginManager\AssetMapper\AssetMapCompiler;
use App\PluginManager\AssetMapper\Exception\AssetCompilationException;
use App\Repository\PluginRepository;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

#[CoversClass(AssetMapCompiler::class)]
class AssetMapCompilerTest extends MockeryTestCase
{
    public function testCompileResetsAssetCompilationRequiredAfterSuccessfulCompilation(): void
    {
        $commandWasRun = false;

        $command = new Command('asset-map:compile');
        $command->setCode(static function () use (&$commandWasRun): int {
            $commandWasRun = true;

            return Command::SUCCESS;
        });

        $kernel = $this->createKernelWithCommand($command);
        $repository = \Mockery::mock(PluginRepository::class);

        $repository
            ->shouldReceive('resetAssetCompilationRequired')
            ->once();

        $compiler = new AssetMapCompiler($repository, $kernel);
        $compiler->compile();

        $this->assertTrue($commandWasRun);
    }

    public function testCompileThrowsExceptionWhenCompilationFails(): void
    {
        $command = new Command('asset-map:compile');
        $command->setCode(static fn (): int => Command::FAILURE);

        $kernel = $this->createKernelWithCommand($command);
        $repository = \Mockery::mock(PluginRepository::class);

        $repository
            ->shouldReceive('resetAssetCompilationRequired')
            ->never();

        $compiler = new AssetMapCompiler($repository, $kernel);

        $this->expectException(AssetCompilationException::class);
        $this->expectExceptionMessage('Asset compilation failed!');

        $compiler->compile();
    }

    private function createKernelWithCommand(Command $command): Kernel
    {
        $commandLoader = new FactoryCommandLoader([
            'asset-map:compile' => static fn (): Command => $command,
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

        return $kernel;
    }
}
