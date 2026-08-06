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

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class IndexController extends AbstractDashboardController
{
    public function __construct(
        private readonly LocaleRepository $localeRepository,
        private readonly RequestStack $requestStack,
        private readonly Packages $assets,
    ) {
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        $currentLocale = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'default';

        $locales = $this->localeRepository->getAvailableLocales();
        $localeMenuItems = [];

        $localeMenuItems[] = MenuItem::section('Language');

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

    public function index(): Response
    {
        return $this->render('@EasyAdmin/layout.html.twig', [
        ]);
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

        yield MenuItem::section('Products', 'fa fa-industry')
            ->setPermission('ROLE_EDITOR');
        yield MenuItem::linkTo(ProductCrudController::class, 'Products', 'fas fa-table-list')
            ->setPermission('ROLE_EDITOR');

        yield MenuItem::section('Settings', 'fa fa-gear')
            ->setPermission('ROLE_ADMIN');
        yield MenuItem::linkTo(LocaleCrudController::class, 'Locales', 'fas fa-globe')
            ->setPermission('ROLE_ADMIN');

        yield MenuItem::subMenu('Types', 'fa fa-layer-group')->setSubItems(
            [
                MenuItem::linkTo(IdentifierTypeCrudController::class, 'Identifier', 'fas fa-fingerprint')
                    ->setPermission('ROLE_ADMIN'),
                MenuItem::linkTo(FieldTypeCrudController::class, 'Field', 'fas fa-rectangle-list')
                    ->setPermission('ROLE_ADMIN'),
                MenuItem::linkTo(ProductTypeCrudController::class, 'Product', 'fas fa-industry')
                    ->setPermission('ROLE_ADMIN'),
            ]
        )->setPermission('ROLE_ADMIN');

        yield MenuItem::linkTo(PluginCrudController::class, 'Plugin Manager', 'fas fa-puzzle-piece')
            ->setPermission('ROLE_SUPER_ADMIN');
        yield MenuItem::linkTo(UserCrudController::class, 'User', 'fas fa-users')
            ->setPermission('ROLE_SUPER_ADMIN');
        // TODO: add route
        yield MenuItem::linkTo(PluginCrudController::class, 'Orphaned Records', 'fas fa-trash-can')
            ->setPermission('ROLE_SUPER_ADMIN');
    }
}
