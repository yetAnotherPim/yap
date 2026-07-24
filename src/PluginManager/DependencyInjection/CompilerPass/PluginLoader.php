<?php

declare(strict_types=1);

namespace App\PluginManager\DependencyInjection\CompilerPass;

use App\PluginManager\PluginManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PluginLoader implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @phpstan-var string $appEnv */
        $appEnv = $container->getParameterBag()->get('kernel.environment');

        PluginManager::loadActivePlugins($appEnv, $container);
    }
}
