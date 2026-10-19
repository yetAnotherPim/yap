<?php

declare(strict_types=1);

namespace App\PluginManager\Event\WorkflowEvent;

use App\Entity\Plugin\Plugin;
use App\PluginManager\Plugin\PluginInterface;

interface PluginWorkflowEventInterface
{
    public function getPluginInstance(): PluginInterface;

    public function getPluginEntity(): Plugin;
}
