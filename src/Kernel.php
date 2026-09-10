<?php

declare(strict_types=1);

namespace App;

use App\PluginManager\PluginManager;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(
        ContainerConfigurator $container,
        LoaderInterface $loader,
        ContainerBuilder $containerBuilder,
    ): void {
        $configDir = preg_replace(
            '{/config$}',
            '/{config}',
            $this->getConfigDir(),
        );

        $container->import($configDir.'/{packages}/*.{php,yaml}');
        $container->import(
            $configDir.'/{packages}/'.$this->environment.'/*.{php,yaml}',
        );

        if (is_file($this->getConfigDir().'/services.yaml')) {
            $container->import($configDir.'/services.yaml');
            $container->import(
                $configDir.'/{services}_'.$this->environment.'.yaml',
            );
        } else {
            $container->import($configDir.'/{services}.php');
            $container->import(
                $configDir.'/{services}_'.$this->environment.'.php',
            );
        }

        PluginManager::loadActivePlugins(
            $this->environment,
            $containerBuilder,
        );
    }
}
