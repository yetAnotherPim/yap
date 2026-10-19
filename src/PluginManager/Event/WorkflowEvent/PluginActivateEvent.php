<?php

declare(strict_types=1);

namespace App\PluginManager\Event\WorkflowEvent;

use App\Entity\Plugin\Plugin;
use App\PluginManager\Plugin\PluginInterface;

class PluginActivateEvent implements PluginWorkflowEventInterface
{
    public function __construct(
        private readonly PluginInterface $pluginInstance,
        private Plugin $pluginEntity,
    ) {
    }

    public function getPluginInstance(): PluginInterface
    {
        return $this->pluginInstance;
    }

    public function getPluginEntity(): Plugin
    {
        return $this->pluginEntity;
    }
}
