<?php

declare(strict_types=1);

namespace App\plugins\JsConsumer\EventSubscriber;

use App\PluginManager\Event\Admin\Assets\AdminAssetsConfiguredEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class AdminAssetsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            AdminAssetsConfiguredEvent::class => 'onAdminAssetsConfigured',
        ];
    }

    public function onAdminAssetsConfigured(AdminAssetsConfiguredEvent $event): void
    {
        $event->getAssets()->addJsFile('plugins/JsConsumer/admin.js');
    }
}
