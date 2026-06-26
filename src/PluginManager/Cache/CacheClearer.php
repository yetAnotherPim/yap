<?php

declare(strict_types=1);

namespace App\PluginManager\Cache;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class CacheClearer
{
    public function __construct(private readonly Kernel $kernel)
    {
    }

    public function clear(): void
    {
        $application = new Application($this->kernel);
        $application->doRun(new ArrayInput(['cache:clear']), new NullOutput());
    }
}
