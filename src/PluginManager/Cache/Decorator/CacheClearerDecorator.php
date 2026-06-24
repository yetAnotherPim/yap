<?php

declare(strict_types=1);

namespace App\PluginManager\Cache\Decorator;

use App\PluginManager\PluginManager;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

#[AsDecorator('cache_clearer')]
class CacheClearerDecorator implements CacheClearerInterface
{
    public function __construct(
        private PluginManager $pluginManager,
        #[AutowireDecorated()]
        private CacheClearerInterface $inner,
    ) {
    }

    public function clear(string $cacheDir): void
    {
        $this->pluginManager->dumpPluginConfiguration();

        $this->inner->clear($cacheDir);
    }
}
