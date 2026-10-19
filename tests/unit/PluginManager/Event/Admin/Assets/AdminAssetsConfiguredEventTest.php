<?php

declare(strict_types=1);

namespace App\Tests\unit\PluginManager\Event\Admin\Assets;

use App\PluginManager\Event\Admin\Assets\AdminAssetsConfiguredEvent;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AdminAssetsConfiguredEvent::class)]
class AdminAssetsConfiguredEventTest extends TestCase
{
    public function testGetAssetsReturnsAssetsPassedToConstructor(): void
    {
        $assets = Assets::new();

        $event = new AdminAssetsConfiguredEvent($assets);

        $this->assertSame($assets, $event->getAssets());
    }
}
