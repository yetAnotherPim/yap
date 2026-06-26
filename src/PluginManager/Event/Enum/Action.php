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

    public static function tryFromName(string $name): ?static
    {
        switch (strtoupper($name)) {
            case 'UPDATE': return self::UPDATE;
            case 'ACTIVATE': return self::ACTIVATE;
            case 'DEACTIVATE': return self::DEACTIVATE;
        }

        return null;
    }
}
