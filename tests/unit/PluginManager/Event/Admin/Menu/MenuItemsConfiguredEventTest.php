<?php

declare(strict_types=1);

namespace App\Tests\unit\PluginManager\Event\Admin\Menu;

use App\PluginManager\Event\Admin\Menu\MenuItemsConfiguredEvent;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MenuItemsConfiguredEvent::class)]
class MenuItemsConfiguredEventTest extends TestCase
{
    public function testGetMenuItemsReturnsInitialMenuItems(): void
    {
        $menuItem1 = \Mockery::mock(MenuItemInterface::class);
        $menuItem2 = \Mockery::mock(MenuItemInterface::class);

        $event = new MenuItemsConfiguredEvent([
            $menuItem1,
            $menuItem2,
        ]);

        $this->assertSame(
            [
                $menuItem1,
                $menuItem2,
            ],
            $event->getMenuItems(),
        );
    }

    public function testAddMenuItemAddsMenuItem(): void
    {
        $menuItem1 = \Mockery::mock(MenuItemInterface::class);
        $menuItem2 = \Mockery::mock(MenuItemInterface::class);

        $event = new MenuItemsConfiguredEvent([
            $menuItem1,
        ]);

        $event->addMenuItem($menuItem2);

        $this->assertSame(
            [
                $menuItem1,
                $menuItem2,
            ],
            $event->getMenuItems(),
        );
    }

    public function testSetMenuItemsReplacesMenuItems(): void
    {
        $menuItem1 = \Mockery::mock(MenuItemInterface::class);
        $menuItem2 = \Mockery::mock(MenuItemInterface::class);
        $menuItem3 = \Mockery::mock(MenuItemInterface::class);

        $event = new MenuItemsConfiguredEvent([
            $menuItem1,
        ]);

        $event->setMenuItems([
            $menuItem2,
            $menuItem3,
        ]);

        $this->assertSame(
            [
                $menuItem2,
                $menuItem3,
            ],
            $event->getMenuItems(),
        );
    }
}
