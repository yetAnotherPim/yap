<?php

declare(strict_types=1);

namespace App\PluginManager\Event\Enum;

use App\PluginManager\Event\WorkflowEvent\PluginActivateEvent;
use App\PluginManager\Event\WorkflowEvent\PluginDeactivateEvent;
use App\PluginManager\Event\WorkflowEvent\PluginUpdateEvent;

enum Action: string
{
    case UPDATE = PluginUpdateEvent::class;
    case ACTIVATE = PluginActivateEvent::class;
    case DEACTIVATE = PluginDeactivateEvent::class;
}
