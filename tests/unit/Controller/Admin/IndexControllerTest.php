<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin;

use App\Controller\Admin\IndexController;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(IndexController::class)]
class IndexControllerTest extends MockeryTestCase
{
    public function testConfigureDashboardSetsTitle(): void
    {
        $controller = new IndexController();

        $dashboard = $controller->configureDashboard();

        self::assertSame(
            'Yap',
            $dashboard->getAsDto()->getTitle()
        );
    }

    public function testConfigureMenuItemsReturnsExpectedMenuItems(): void
    {
        $controller = new IndexController();

        $items = iterator_to_array(
            $controller->configureMenuItems()
        );

        self::assertCount(7, $items);

        self::assertSame(
            'Home',
            $items[0]->getAsDto()->getLabel()
        );

        self::assertSame(
            'Products',
            $items[1]->getAsDto()->getLabel()
        );

        self::assertSame(
            'Products',
            $items[2]->getAsDto()->getLabel()
        );

        self::assertSame(
            'Settings',
            $items[3]->getAsDto()->getLabel()
        );

        self::assertSame(
            'Locales',
            $items[4]->getAsDto()->getLabel()
        );

        self::assertSame(
            'Types',
            $items[5]->getAsDto()->getLabel()
        );

        self::assertSame(
            'Orphaned Records',
            $items[6]->getAsDto()->getLabel()
        );
    }

    public function testConfigureMenuItemsContainsTypeSubmenuEntries(): void
    {
        $controller = new IndexController();

        $items = iterator_to_array(
            $controller->configureMenuItems()
        );

        $submenu = $items[5];

        $subItems = $submenu
            ->getAsDto()
            ->getSubItems();

        self::assertCount(3, $subItems);

        self::assertSame(
            'Identifier',
            $subItems[0]->getLabel()
        );

        self::assertSame(
            'Field',
            $subItems[1]->getLabel()
        );

        self::assertSame(
            'Product',
            $subItems[2]->getLabel()
        );
    }

    public function testIndexRendersEasyAdminLayout(): void
    {
        $expectedResponse = new Response();

        $controller = \Mockery::mock(IndexController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('render')
            ->once()
            ->with(
                '@EasyAdmin/layout.html.twig',
                []
            )
            ->andReturn($expectedResponse);

        $response = $controller->index();

        self::assertSame(
            $expectedResponse,
            $response
        );
    }
}
