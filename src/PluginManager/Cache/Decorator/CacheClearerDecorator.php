<?php

declare(strict_types=1);

namespace App\PluginManager\Cache\Decorator;

use App\PluginManager\PluginManager;
use Doctrine\DBAL\Exception as DoctrineException;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

#[AsDecorator('cache_clearer')]
class CacheClearerDecorator implements CacheClearerInterface
{
    public function __construct(
        private PluginManager $pluginManager,
        #[AutowireDecorated]
        private CacheClearerInterface $inner,
    ) {
    }

    public function clear(string $cacheDir): void
    {
        try {
            $this->pluginManager->dumpPluginConfiguration();
        } catch (DoctrineException $exception) {
            // on installing dependencies db isn't configured yet
        }

        $this->inner->clear($cacheDir);
    }
}
