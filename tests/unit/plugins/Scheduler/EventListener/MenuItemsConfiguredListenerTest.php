<?php

declare(strict_types=1);

namespace App\Tests\unit\plugins\Scheduler\EventListener;

use App\PluginManager\Event\Admin\Menu\MenuItemsConfiguredEvent;
use App\plugins\Scheduler\EventListener\MenuItemsConfiguredListener;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Translation\TranslatableMessage;

#[CoversClass(MenuItemsConfiguredListener::class)]
class MenuItemsConfiguredListenerTest extends MockeryTestCase
{
    private MenuItemsConfiguredEvent&Mockery\MockInterface $event;
    private MenuItemsConfiguredListener $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->event = \Mockery::mock(MenuItemsConfiguredEvent::class);
        $this->listener = new MenuItemsConfiguredListener();
    }

    public function testOnCustomEventAddsMenuItems(): void
    {
        $this->event
            ->shouldReceive('addMenuItem')
            ->once()
            ->with(
                \Mockery::on(
                    function (MenuItemInterface $menuItem): bool {
                        $dto = $menuItem->getAsDto();
                        $label = $dto->getLabel();

                        $this->assertInstanceOf(
                            TranslatableMessage::class,
                            $label,
                        );

                        $this->assertSame(
                            'yap_scheduler.title',
                            $label->getMessage(),
                        );

                        $this->assertSame(
                            'scheduler',
                            $label->getDomain(),
                        );

                        $this->assertSame(
                            'fa fa-clock',
                            $dto->getIcon(),
                        );

                        $this->assertSame(
                            'ROLE_ADMIN',
                            $dto->getPermission(),
                        );

                        return true;
                    },
                ),
            );

        $this->event
            ->shouldReceive('addMenuItem')
            ->once()
            ->with(
                \Mockery::on(
                    function (MenuItemInterface $menuItem): bool {
                        $dto = $menuItem->getAsDto();
                        $label = $dto->getLabel();

                        $this->assertInstanceOf(
                            TranslatableMessage::class,
                            $label,
                        );

                        $this->assertSame(
                            'yap_scheduler.tasks.title',
                            $label->getMessage(),
                        );

                        $this->assertSame(
                            'scheduler',
                            $label->getDomain(),
                        );

                        $this->assertSame(
                            'fa fa-list-check',
                            $dto->getIcon(),
                        );

                        $this->assertSame(
                            'ROLE_ADMIN',
                            $dto->getPermission(),
                        );

                        $this->assertSame(
                            'yap_scheduler_tasks',
                            $dto->getRouteName(),
                        );

                        return true;
                    },
                ),
            );

        $this->listener->onCustomEvent($this->event);
    }
}
