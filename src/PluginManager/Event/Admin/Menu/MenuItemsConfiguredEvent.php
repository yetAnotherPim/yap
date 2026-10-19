<?php

declare(strict_types=1);

namespace App\PluginManager\Event\Admin\Menu;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use Symfony\Contracts\EventDispatcher\Event;

class MenuItemsConfiguredEvent extends Event
{
    /**
     * @param list<MenuItemInterface> $menuItems
     */
    public function __construct(
        private array $menuItems,
    ) {
    }

    /**
     * @return list<MenuItemInterface>
     */
    public function getMenuItems(): array
    {
        return $this->menuItems;
    }

    public function addMenuItem(MenuItemInterface $menuItem): void
    {
        $this->menuItems[] = $menuItem;
    }

    /**
     * @param list<MenuItemInterface> $menuItems
     */
    public function setMenuItems(array $menuItems): void
    {
        $this->menuItems = $menuItems;
    }
}
