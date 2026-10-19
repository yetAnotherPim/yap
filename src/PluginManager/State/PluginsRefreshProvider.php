<?php

declare(strict_types=1);

namespace App\PluginManager\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Plugin\Plugin;
use App\PluginManager\Cache\CacheClearer;
use App\PluginManager\PluginManager;

/** @implements ProviderInterface<Plugin> */
class PluginsRefreshProvider implements ProviderInterface
{
    public function __construct(
        private readonly CacheClearer $cacheClearer,
        private readonly PluginManager $pluginManager,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->cacheClearer->clear();

        return $this->pluginManager->getPlugins();
    }
}
