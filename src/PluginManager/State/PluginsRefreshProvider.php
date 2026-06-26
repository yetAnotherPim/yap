<?php

declare(strict_types=1);

namespace App\PluginManager\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Plugin\Plugin;
use App\PluginManager\Cache\CacheClearer;
use App\PluginManager\PluginManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/** @implements ProviderInterface<Plugin> */
class PluginsRefreshProvider implements ProviderInterface
{
    /** @phpstan-param ProviderInterface<Plugin> $collectionProvider */
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private readonly ProviderInterface $collectionProvider,
        private readonly CacheClearer $cacheClearer,
        private readonly PluginManager $pluginManager,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $this->cacheClearer->clear();
        $this->pluginManager->getPlugins();

        return $this->collectionProvider->provide($operation, $uriVariables, $context);
    }
}
