<?php

declare(strict_types=1);

namespace App\PluginManager\AssetMapper;

use App\Kernel;
use App\PluginManager\AssetMapper\Exception\AssetCompilationException;
use App\Repository\PluginRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class AssetMapCompiler
{
    public function __construct(private readonly PluginRepository $pluginRepository, private readonly Kernel $kernel)
    {
    }

    /**
     * @throws AssetCompilationException
     */
    public function compile(): void
    {
        $application = new Application($this->kernel);

        $exitCode = $application->doRun(
            new ArrayInput(['command' => 'asset-map:compile']),
            new NullOutput(),
        );

        if (0 !== $exitCode) {
            throw new AssetCompilationException('Asset compilation failed!');
        }

        $this->pluginRepository->resetAssetCompilationRequired();
    }
}
