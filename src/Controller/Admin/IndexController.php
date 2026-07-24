<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class IndexController extends AbstractDashboardController
{
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
        return Dashboard::new()
            ->setTitle('Yap');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Home', 'fa fa-home');

        yield MenuItem::section('Products', 'fa fa-industry');
        yield MenuItem::linkTo(ProductCrudController::class, 'Products', 'fas fa-table-list');

        yield MenuItem::section('Settings', 'fa fa-gear');
        yield MenuItem::linkTo(LocaleCrudController::class, 'Locales', 'fas fa-globe');

        yield MenuItem::subMenu('Types', 'fa fa-layer-group')->setSubItems(
            [
                MenuItem::linkTo(IdentifierTypeCrudController::class, 'Identifier', 'fas fa-fingerprint'),
                MenuItem::linkTo(FieldTypeCrudController::class, 'Field', 'fas fa-rectangle-list'),
                MenuItem::linkTo(ProductTypeCrudController::class, 'Product', 'fas fa-industry'),
            ]
        );

        yield MenuItem::linkTo(PluginCrudController::class, 'Plugin Manager', 'fas fa-puzzle-piece');

        yield MenuItem::linkTo(OrphanDashboardController::class, 'Orphaned Records', 'fas fa-trash-can');
    }
}
