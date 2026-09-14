<?php

declare(strict_types=1);

namespace App\plugins\Scheduler\EventListener;

use App\Event\Admin\Menu\MenuItemsConfiguredEvent;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Translation\TranslatableMessage;

class MenuItemsConfiguredListener
{
    #[AsEventListener]
    public function onCustomEvent(MenuItemsConfiguredEvent $event): void
    {
        $event->addMenuItem(
            MenuItem::section(new TranslatableMessage('yap_scheduler.title', [], 'scheduler'), 'fa fa-clock')
                ->setPermission('ROLE_ADMIN'),
        );

        $event->addMenuItem(
            MenuItem::linkToRoute(
                new TranslatableMessage(
                    'yap_scheduler.tasks.title',
                    [],
                    'scheduler',
                ),
                'fa fa-list-check',
                'yap_scheduler_tasks',
            )->setPermission('ROLE_ADMIN'),
        );
    }
}
