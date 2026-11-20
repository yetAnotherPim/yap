<?php

declare(strict_types=1);

namespace App\plugins\Feeds\EventListener;

use App\PluginManager\Event\Admin\Menu\MenuItemsConfiguredEvent;
use App\plugins\Feeds\Controller\Admin\FeedCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

class MenuItemsConfiguredListener
{
    #[AsEventListener]
    public function onCustomEvent(MenuItemsConfiguredEvent $event): void
    {
        $event->addMenuItem(
            MenuItem::section('Feeds', 'fa fa-square-rss')
                ->setPermission('ROLE_ADMIN'),
        );

        $event->addMenuItem(
            MenuItem::linkTo(
                FeedCrudController::class,
                'Feeds',
                'fa fa-rss',
            )->setPermission('ROLE_ADMIN'),
        );
    }
}
