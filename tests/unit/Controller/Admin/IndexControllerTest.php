<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin;

use App\Controller\Admin\IndexController;
use App\Repository\LocaleRepository;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(IndexController::class)]
class IndexControllerTest extends MockeryTestCase
{
    private LocaleRepository $localeRepository;
    private RequestStack $requestStack;

    private IndexController $controller;

    public function setUp(): void
    {
        $this->localeRepository = \Mockery::mock(LocaleRepository::class);
        $this->requestStack = \Mockery::mock(RequestStack::class);

        $this->controller = new IndexController($this->localeRepository, $this->requestStack);

        parent::setUp();
    }

    public function testConfigureAssets(): void
    {
        $assets = $this->controller->configureAssets();

        $this->assertArrayHasKey('admin', $assets->getAsDto()->getAssetMapperAssets());
    }

    public function testConfigureDashboardSetsTitle(): void
    {
        $dashboard = $this->controller->configureDashboard();

        $this->assertSame(
            'Yap',
            $dashboard->getAsDto()->getTitle()
        );
    }

    public function testConfigureMenuItemsReturnsExpectedMenuItems(): void
    {
        $items = iterator_to_array(
            $this->controller->configureMenuItems()
        );

        $this->assertCount(9, $items);

        $this->assertSame(
            'Home',
            $items[0]->getAsDto()->getLabel()
        );

        $this->assertSame(
            'Products',
            $items[1]->getAsDto()->getLabel()
        );

        $this->assertSame(
            'Products',
            $items[2]->getAsDto()->getLabel()
        );

        $this->assertSame(
            'Settings',
            $items[3]->getAsDto()->getLabel()
        );

        $this->assertSame(
            'Locales',
            $items[4]->getAsDto()->getLabel()
        );

        $this->assertSame(
            'Types',
            $items[5]->getAsDto()->getLabel()
        );

        $this->assertSame(
            'Plugin Manager',
            $items[6]->getAsDto()->getLabel()
        );

        $this->assertSame(
            'User',
            $items[7]->getAsDto()->getLabel()
        );

        $this->assertSame(
            'Orphaned Records',
            $items[8]->getAsDto()->getLabel()
        );
    }

    public function testConfigureMenuItemsContainsTypeSubmenuEntries(): void
    {
        $items = iterator_to_array(
            $this->controller->configureMenuItems()
        );

        $submenu = $items[5];

        $subItems = $submenu
            ->getAsDto()
            ->getSubItems();

        $this->assertCount(3, $subItems);

        $this->assertSame(
            'Identifier',
            $subItems[0]->getLabel()
        );

        $this->assertSame(
            'Field',
            $subItems[1]->getLabel()
        );

        $this->assertSame(
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

        $this->assertSame(
            $expectedResponse,
            $response
        );
    }
}
