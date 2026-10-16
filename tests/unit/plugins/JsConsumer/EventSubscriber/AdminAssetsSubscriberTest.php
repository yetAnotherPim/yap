<?php

declare(strict_types=1);

namespace App\Tests\unit\plugins\JsConsumer\EventSubscriber;

use App\PluginManager\Event\Admin\Assets\AdminAssetsConfiguredEvent;
use App\plugins\JsConsumer\EventSubscriber\AdminAssetsSubscriber;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(AdminAssetsSubscriber::class)]
#[UsesClass(AdminAssetsConfiguredEvent::class)]
class AdminAssetsSubscriberTest extends MockeryTestCase
{
    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                AdminAssetsConfiguredEvent::class => 'onAdminAssetsConfigured',
            ],
            AdminAssetsSubscriber::getSubscribedEvents(),
        );
    }

    public function testOnAdminAssetsConfiguredAddsJavaScriptFile(): void
    {
        $assets = Assets::new();
        $event = new AdminAssetsConfiguredEvent($assets);

        $subscriber = new AdminAssetsSubscriber();

        $subscriber->onAdminAssetsConfigured($event);

        $this->assertArrayHasKey(
            'plugins/JsConsumer/admin.js',
            $assets->getAsDto()->getJsAssets(),
        );
    }
}
