<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Admin\Products\ProductCrudController;
use App\Controller\Admin\Settings\FieldTypeCrudController;
use App\Controller\Admin\Settings\IdentifierTypeCrudController;
use App\Controller\Admin\Settings\LocaleCrudController;
use App\Controller\Admin\Settings\PluginCrudController;
use App\Controller\Admin\Settings\ProductTypeCrudController;
use App\Controller\Admin\Settings\UserCrudController;
use App\Controller\Provider\DashboardStatisticsProvider;
use App\Controller\Provider\DashboardSystemStatusProvider;
use App\Entity\Locale\Locale;
use App\Repository\LocaleRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatableMessage;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class IndexController extends AbstractDashboardController
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
        private readonly RequestStack $requestStack,
        private readonly Packages $assets,
        private readonly DashboardStatisticsProvider $dashboardStatisticsProvider,
        private readonly DashboardSystemStatusProvider $dashboardSystemStatusProvider,
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function index(): Response
    {
        return $this->render('easyadmin/page/home.hwtml.twig', [
            'statistics' => $this->dashboardStatisticsProvider->getStatistics(),
            'systemMessages' => $this->dashboardSystemStatusProvider->getMessages(),
        ]);
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        $currentLocale = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'default';

        $locales = $this->localeRepository->getAvailableLocales();
        $localeMenuItems = [];

        $localeMenuItems[] = MenuItem::section(new TranslatableMessage('general.labels.language', [], 'admin'), 'fa fa-globe');

        foreach ($locales as $locale) {
            $isActive = ($currentLocale === $locale->getId());

            $localeMenuItems[] = MenuItem::linkToRoute(
                (true === $locale->isDefault()) ? $locale->getId().' (default)' : $locale->getId(),
                $isActive ? 'fa fa-check text-success' : 'fa fa-language',
                'admin_switch_locale',
                ['localeId' => $locale->getId()]
            );
        }

        return parent::configureUserMenu($user)
            ->addMenuItems($localeMenuItems);
    }

    #[Route('/admin/switch-locale/{localeId}', name: 'admin_switch_locale')]
    public function switchLocale(string $localeId, Request $request): Response
    {
        $isValidLocale = $this->localeRepository->getAvailableLocales()->exists(
            static fn (int|string $key, Locale $locale): bool => $locale->getId() === $localeId
        );

        if (true === $isValidLocale) {
            $request->getSession()->set('_locale', $localeId);
        }

        $referer = $request->headers->get('referer');

        return $this->redirect($referer ?: $this->generateUrl('admin'));
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addAssetMapperEntry('admin');
    }

    public function configureDashboard(): Dashboard
    {
        $logoUrl = $this->assets->getUrl(
            'images/logo/yap-logo-header.webp'
        );

        $faviconUrl = $this->assets->getUrl(
            'images/favicon/favicon.svg'
        );

        return Dashboard::new()
            ->setTitle(\sprintf(
                '<img src="%s" alt="YAP" class="yap-admin-logo">',
                htmlspecialchars($logoUrl, ENT_QUOTES),
            ))
            ->setFaviconPath($faviconUrl);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Home', 'fa fa-home');

        yield MenuItem::section(new TranslatableMessage('product.title', [], 'admin'), 'fa fa-industry')
            ->setPermission('ROLE_EDITOR');
        yield MenuItem::linkTo(ProductCrudController::class, new TranslatableMessage('product.title', [], 'admin'), 'fas fa-table-list')
            ->setPermission('ROLE_EDITOR');

        yield MenuItem::section(new TranslatableMessage('settings.title', [], 'admin'), 'fa fa-gear')
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linkTo(LocaleCrudController::class, new TranslatableMessage('locale.title', [], 'admin'), 'fas fa-globe')
            ->setPermission('ROLE_ADMIN');

        yield MenuItem::subMenu(new TranslatableMessage('types.title', [], 'admin'), 'fa fa-layer-group')->setSubItems(
            [
                MenuItem::linkTo(IdentifierTypeCrudController::class, new TranslatableMessage('types.label.identifier', [], 'admin'), 'fas fa-fingerprint')
                    ->setPermission('ROLE_ADMIN'),
                MenuItem::linkTo(FieldTypeCrudController::class, new TranslatableMessage('types.label.field', [], 'admin'), 'fas fa-rectangle-list')
                    ->setPermission('ROLE_ADMIN'),
                MenuItem::linkTo(ProductTypeCrudController::class, new TranslatableMessage('types.label.product', [], 'admin'), 'fas fa-industry')
                    ->setPermission('ROLE_ADMIN'),
            ]
        )->setPermission('ROLE_ADMIN');

        yield MenuItem::linkTo(PluginCrudController::class, new TranslatableMessage('plugin_manager.title', [], 'admin'), 'fas fa-puzzle-piece')
            ->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkTo(UserCrudController::class, new TranslatableMessage('user.title', [], 'admin'), 'fas fa-users')
            ->setPermission('ROLE_SUPER_ADMIN');

        //        yield MenuItem::linkTo(PluginCrudController::class, 'Orphaned Records', 'fas fa-trash-can')
        //            ->setPermission('ROLE_SUPER_ADMIN');
    }
}
