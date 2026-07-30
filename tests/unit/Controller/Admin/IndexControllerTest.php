<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin;

use App\Controller\Admin\IndexController;
use App\Entity\Locale\Locale;
use App\Repository\LocaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[CoversClass(IndexController::class)]
class IndexControllerTest extends MockeryTestCase
{
    /** @var LocaleRepository|MockInterface */
    private LocaleRepository $localeRepository;

    /** @var RequestStack|MockInterface */
    private RequestStack $requestStack;

    private IndexController $controller;

    public function setUp(): void
    {
        $this->localeRepository = \Mockery::mock(LocaleRepository::class);
        $this->requestStack = \Mockery::mock(RequestStack::class);

        $this->controller = new IndexController($this->localeRepository, $this->requestStack);

        parent::setUp();
    }

    public function testConfigureUserMenuCreatesLocaleMenu(): void
    {
        $containerMock = \Mockery::mock(ContainerInterface::class);
        $securityCheckerMock = \Mockery::mock(AuthorizationCheckerInterface::class);

        $this->controller->setContainer($containerMock);

        $request = \Mockery::mock(Request::class);
        $request
            ->shouldReceive('getLocale')
            ->once()
            ->andReturn('de_DE');

        $this->requestStack
            ->shouldReceive('getCurrentRequest')
            ->once()
            ->andReturn($request);

        $deLocale = \Mockery::mock(Locale::class);
        $deLocale
            ->shouldReceive('getId')
            ->times(3)
            ->andReturn('de_DE');

        $deLocale
            ->shouldReceive('isDefault')
            ->once()
            ->andReturn(true);

        $enLocale = \Mockery::mock(Locale::class);
        $enLocale
            ->shouldReceive('getId')
            ->times(3)
            ->andReturn('en_GB');

        $enLocale
            ->shouldReceive('isDefault')
            ->once()
            ->andReturn(false);

        $this->localeRepository
            ->shouldReceive('getAvailableLocales')
            ->once()
            ->andReturn(new ArrayCollection([$deLocale, $enLocale]));

        $containerMock
            ->shouldReceive('has')
            ->with('security.authorization_checker')
            ->andReturn(true);

        $containerMock
            ->shouldReceive('get')
            ->with('security.authorization_checker')
            ->andReturn($securityCheckerMock);

        $securityCheckerMock
            ->shouldReceive('isGranted')
            ->once()
            ->andReturn(true);

        $user = \Mockery::mock(UserInterface::class);

        $menu = $this->controller->configureUserMenu($user);

        $items = iterator_to_array($menu->getAsDto()->getItems());

        $this->assertSame('Language', $items[0]->getLabel());
        $this->assertSame('de_DE (default)', $items[1]->getLabel());
        $this->assertSame('fa fa-check text-success', $items[1]->getIcon());
        $this->assertSame('en_GB', $items[2]->getLabel());
        $this->assertSame('fa fa-language', $items[2]->getIcon());
    }

    public function testSwitchLocaleStoresLocaleInSessionAndRedirectsToReferer(): void
    {
        $locale = \Mockery::mock(Locale::class);
        $locale
            ->shouldReceive('getId')
            ->once()
            ->andReturn('de_DE');

        $locales = new ArrayCollection([$locale]);

        $this->localeRepository
            ->shouldReceive('getAvailableLocales')
            ->once()
            ->andReturn($locales);

        $session = \Mockery::mock(SessionInterface::class);
        $session
            ->shouldReceive('set')
            ->once()
            ->with('_locale', 'de_DE');

        $request = Request::create('/admin/switch-locale/de');
        $request->headers->set('referer', '/admin/product');
        $request->setSession($session);

        $response = $this->controller->switchLocale('de_DE', $request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/admin/product', $response->getTargetUrl());
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
