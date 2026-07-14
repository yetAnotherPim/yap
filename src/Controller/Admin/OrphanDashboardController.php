<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin/settings/orphans', routeName: 'admin_settings_orphans')]
class OrphanDashboardController extends IndexController
{
    public function index(): Response
    {
        return parent::index();

        // TODO: display records
        // TODO: add clear button
    }
}
