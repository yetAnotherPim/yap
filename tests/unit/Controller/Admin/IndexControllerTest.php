<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin;

use App\Controller\Admin\IndexController;
use App\Controller\Admin\User\AccountCrudController;
use App\Controller\Provider\DashboardStatisticsProvider;
use App\Controller\Provider\DashboardSystemStatusProvider;
use App\Entity\Locale\Locale;
use App\Entity\User\User;
use App\PluginManager\Event\Admin\Assets\AdminAssetsConfiguredEvent;
use App\PluginManager\Event\Admin\Menu\MenuItemsConfiguredEvent;
use App\Repository\LocaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Container\ContainerInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(IndexController::class)]
#[UsesClass(MenuItemsConfiguredEvent::class)]
#[UsesClass(AdminAssetsConfiguredEvent::class)]
class IndexControllerTest extends MockeryTestCase
{
    /** @var LocaleRepository|MockInterface */
    private LocaleRepository $localeRepository;

    /** @var RequestStack|MockInterface */
    private RequestStack $requestStack;

    /** @var Packages|MockInterface */
    private Packages $assets;

    /** @var DashboardStatisticsProvider|MockInterface */
    private DashboardStatisticsProvider $dashboardStatisticsProvider;

    /** @var DashboardSystemStatusProvider|MockInterface */
    private DashboardSystemStatusProvider $dashboardSystemStatusProvider;

    /** @var EventDispatcherInterface|MockInterface */
    private EventDispatcherInterface $eventDispatcher;

    private IndexController $controller;

    public function setUp(): void
    {
        $this->localeRepository = \Mockery::mock(LocaleRepository::class);
        $this->requestStack = \Mockery::mock(RequestStack::class);
        $this->assets = \Mockery::mock(Packages::class);
        $this->dashboardStatisticsProvider = \Mockery::mock(DashboardStatisticsProvider::class);
        $this->dashboardSystemStatusProvider = \Mockery::mock(DashboardSystemStatusProvider::class);
        $this->eventDispatcher = \Mockery::mock(EventDispatcherInterface::class);

        $this->controller = new IndexController(
            $this->localeRepository,
            $this->requestStack,
            $this->assets,
            $this->dashboardStatisticsProvider,
            $this->dashboardSystemStatusProvider,
            $this->eventDispatcher
        );

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

        $user = \Mockery::mock(User::class);
        $userId = UuidV7::v7();

        $adminUrlGenerator = \Mockery::mock(\stdClass::class);
        $adminUrlGenerator
            ->shouldReceive('setController')
            ->once()
            ->with(AccountCrudController::class)
            ->andReturnSelf();

        $adminUrlGenerator
            ->shouldReceive('setAction')
            ->once()
            ->with(Action::EDIT)
            ->andReturnSelf();

        $adminUrlGenerator
            ->shouldReceive('setEntityId')
            ->once()
            ->with($userId)
            ->andReturnSelf();

        $adminUrlGenerator
            ->shouldReceive('generateUrl')
            ->once()
            ->andReturn('/admin?crudAction=index');

        $containerMock->shouldReceive('get')
            ->once()
            ->with(AdminUrlGeneratorInterface::class)
            ->andReturn($adminUrlGenerator);

        $user
            ->shouldReceive('getId')
            ->once()
            ->andReturn($userId);

        $menu = $this->controller->configureUserMenu($user);

        $items = iterator_to_array($menu->getAsDto()->getItems());

        $this->assertInstanceOf(TranslatableMessage::class, $items[0]->getLabel());
        $this->assertInstanceOf(TranslatableMessage::class, $items[1]->getLabel());

        $this->assertInstanceOf(TranslatableMessage::class, $items[2]->getLabel());
        $this->assertSame('de_DE (default)', $items[3]->getLabel());
        $this->assertSame('fa fa-check text-success', $items[3]->getIcon());
        $this->assertSame('en_GB', $items[4]->getLabel());
        $this->assertSame('fa fa-language', $items[4]->getIcon());
    }

    public function testConfigureUserMenuWithInvalidUser(): void
    {
        $containerMock = \Mockery::mock(ContainerInterface::class);
        $securityCheckerMock = \Mockery::mock(AuthorizationCheckerInterface::class);

        $this->controller->setContainer($containerMock);
        $user = \Mockery::mock(UserInterface::class);

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

        $menu = $this->controller->configureUserMenu($user);

        $items = iterator_to_array($menu->getAsDto()->getItems());

        $this->assertInstanceOf(TranslatableMessage::class, $items[1]->getLabel());
        $this->assertSame('user.sign_out', $items[1]->getLabel()->getMessage());
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
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(AdminAssetsConfiguredEvent::class));

        $assets = $this->controller->configureAssets();

        $this->assertArrayHasKey('admin', $assets->getAsDto()->getAssetMapperAssets());
    }

    public function testConfigureDashboard(): void
    {
        $this->assets
            ->shouldReceive('getUrl')
            ->once()
            ->with('images/logo/yap-logo-header.webp')
            ->andReturn('assets/images/favicon/yap-logo-header.webp');

        $this->assets
            ->shouldReceive('getUrl')
            ->once()
            ->with('images/favicon/favicon.svg')
            ->andReturn('assets/images/favicon/favicon.svg');

        $dashboard = $this->controller->configureDashboard();

        $this->assertSame(
            '<img src="assets/images/favicon/yap-logo-header.webp" alt="YAP" class="yap-admin-logo">',
            $dashboard->getAsDto()->getTitle()
        );

        $this->assertSame(
            'assets/images/favicon/favicon.svg',
            $dashboard->getAsDto()->getFaviconPath()
        );
    }

    public function testConfigureMenuItemsReturnsExpectedMenuItems(): void
    {
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(MenuItemsConfiguredEvent::class))
            ->andReturnUsing(
                static fn (MenuItemsConfiguredEvent $event): MenuItemsConfiguredEvent => $event,
            );

        /** @var list<MenuItemInterface> $items */
        $items = $this->controller->configureMenuItems();

        $this->assertCount(8, $items);

        $this->assertSame(
            'Home',
            $items[0]->getAsDto()->getLabel()
        );

        $this->assertInstanceof(TranslatableMessage::class, $items[1]->getAsDto()->getLabel());
        $this->assertSame(
            'product.title',
            $items[1]->getAsDto()->getLabel()->getMessage()
        );

        $this->assertInstanceof(TranslatableMessage::class, $items[2]->getAsDto()->getLabel());
        $this->assertSame(
            'product.title',
            $items[2]->getAsDto()->getLabel()->getMessage()
        );

        $this->assertInstanceof(TranslatableMessage::class, $items[3]->getAsDto()->getLabel());
        $this->assertSame(
            'settings.title',
            $items[3]->getAsDto()->getLabel()->getMessage()
        );

        $this->assertInstanceof(TranslatableMessage::class, $items[4]->getAsDto()->getLabel());
        $this->assertSame(
            'locale.title',
            $items[4]->getAsDto()->getLabel()->getMessage()
        );

        $this->assertInstanceof(TranslatableMessage::class, $items[5]->getAsDto()->getLabel());
        $this->assertSame(
            'types.title',
            $items[5]->getAsDto()->getLabel()->getMessage()
        );

        $this->assertInstanceof(TranslatableMessage::class, $items[6]->getAsDto()->getLabel());
        $this->assertSame(
            'plugin_manager.title',
            $items[6]->getAsDto()->getLabel()->getMessage()
        );

        $this->assertInstanceof(TranslatableMessage::class, $items[7]->getAsDto()->getLabel());
        $this->assertSame(
            'user.title',
            $items[7]->getAsDto()->getLabel()->getMessage()
        );
    }

    public function testConfigureMenuItemsContainsTypeSubmenuEntries(): void
    {
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(MenuItemsConfiguredEvent::class))
            ->andReturnUsing(
                static fn (MenuItemsConfiguredEvent $event): MenuItemsConfiguredEvent => $event,
            );

        /** @var list<MenuItemInterface> $items */
        $items = $this->controller->configureMenuItems();

        $submenu = $items[5];

        $subItems = $submenu
            ->getAsDto()
            ->getSubItems();

        $this->assertCount(3, $subItems);

        $this->assertInstanceof(TranslatableMessage::class, $subItems[0]->getLabel());
        $this->assertSame(
            'types.label.identifier',
            $subItems[0]->getLabel()->getMessage()
        );

        $this->assertInstanceof(TranslatableMessage::class, $subItems[1]->getLabel());
        $this->assertSame(
            'types.label.field',
            $subItems[1]->getLabel()->getMessage()
        );

        $this->assertInstanceof(TranslatableMessage::class, $subItems[2]->getLabel());
        $this->assertSame(
            'types.label.product',
            $subItems[2]->getLabel()->getMessage()
        );
    }

    public function testIndexRendersEasyAdminLayout(): void
    {
        $expectedResponse = new Response();

        $containerMock = \Mockery::mock(ContainerInterface::class);
        $this->controller->setContainer($containerMock);

        $this->dashboardStatisticsProvider
            ->shouldReceive('getStatistics')
            ->once()
            ->andReturn(['stat1' => 1]);

        $this->dashboardSystemStatusProvider
            ->shouldReceive('getMessages')
            ->once()
            ->andReturn([]);

        $containerMock
            ->shouldReceive('has')
            ->with('twig')
            ->andReturn(true);

        $twigMock = \Mockery::mock(\Twig\Environment::class);
        $containerMock
            ->shouldReceive('get')
            ->with('twig')
            ->andReturn($twigMock);

        $twigMock
            ->shouldReceive('render')
            ->once()
            ->with(
                'easyadmin/page/home.hwtml.twig',
                [
                    'statistics' => ['stat1' => 1],
                    'systemMessages' => [],
                ]
            )
            ->andReturn('rendered content');

        $response = $this->controller->index();

        $this->assertSame(
            200,
            $response->getStatusCode()
        );
        $this->assertSame(
            'rendered content',
            $response->getContent()
        );
    }
}
