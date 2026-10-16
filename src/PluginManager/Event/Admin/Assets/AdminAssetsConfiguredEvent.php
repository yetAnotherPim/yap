<?php

declare(strict_types=1);

namespace App\PluginManager\Event\Admin\Assets;

use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use Symfony\Contracts\EventDispatcher\Event;

class AdminAssetsConfiguredEvent extends Event
{
    public function __construct(
        private Assets $assets,
    ) {
    }

    public function getAssets(): Assets
    {
        return $this->assets;
    }
}
