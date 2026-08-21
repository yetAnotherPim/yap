<?php

declare(strict_types=1);

namespace App\Tests\functional\frontend\Controller\Admin;

use App\Controller\Admin\IndexController;
use App\Controller\Admin\Login\LoginController;
use App\Controller\Admin\Products\IdentifierCrudController;
use App\Controller\Admin\Products\ProductCrudController;
use App\Controller\Admin\Settings\FieldTypeCrudController;
use App\Controller\Admin\Settings\IdentifierTypeCrudController;
use App\Controller\Admin\Settings\LocaleCrudController;
use App\Controller\Admin\Settings\PluginCrudController;
use App\Controller\Admin\Settings\ProductTypeCrudController;
use App\Controller\Admin\Settings\ProductTypeFieldConfigurationCrudController;
use App\Controller\Admin\Settings\UserCrudController;
use App\Doctrine\Event\EventListener\RelatedTimestampUpdater;
use App\Entity\Locale\Locale;
use App\Entity\User\Enum\Roles;
use App\Entity\User\User;
use App\Form\Product\Fields\Renderer\ProductFieldsRenderer;
use App\Form\Product\Fields\Renderer\Registry\RendererRegistry;
use App\Form\Product\Fields\Synchronizer\FieldSynchronizer;
use App\Localisation\Event\EventListener\LocaleListener;
use App\Localisation\Event\EventListener\LoginSuccessListener;
use App\Localisation\Exception\DefaultLocaleIsNotDefinedException;
use App\PluginManager\Cache\CacheClearer;
use App\PluginManager\PluginManager;
use App\Repository\IdentifierTypeRepository;
use App\Repository\LocaleRepository;
use App\Repository\PluginRepository;
use App\Repository\UserRepository;
use App\Tests\helper\Trait\Frontend\LoginTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(LoginController::class)]
#[CoversClass(User::class)]
#[CoversClass(UserRepository::class)]
#[CoversClass(LocaleListener::class)]
#[CoversClass(LoginSuccessListener::class)]
#[CoversClass(DefaultLocaleIsNotDefinedException::class)]
#[CoversClass(Locale::class)]
#[CoversClass(LocaleRepository::class)]
#[CoversClass(PluginManager::class)]
#[CoversClass(PluginRepository::class)]
#[CoversClass(CacheClearer::class)]
#[UsesClass(FieldTypeCrudController::class)]
#[UsesClass(IdentifierCrudController::class)]
#[UsesClass(IdentifierTypeCrudController::class)]
#[UsesClass(IdentifierTypeRepository::class)]
#[UsesClass(LocaleCrudController::class)]
#[UsesClass(ProductTypeCrudController::class)]
#[UsesClass(ProductCrudController::class)]
#[UsesClass(ProductTypeFieldConfigurationCrudController::class)]
#[UsesClass(PluginCrudController::class)]
#[UsesClass(UserCrudController::class)]
#[UsesClass(IndexController::class)]
#[UsesClass(Roles::class)]
#[UsesClass(RelatedTimestampUpdater::class)]
#[UsesClass(ProductFieldsRenderer::class)]
#[UsesClass(RendererRegistry::class)]
#[UsesClass(FieldSynchronizer::class)]
class IndexControllerTest extends WebTestCase
{
    use LoginTrait;

    private const array ADMIN_ROUTES = [
        '/admin',
        '/admin/product',
        '/admin/locale',
        '/admin/identifier-type',
        '/admin/field-type',
        '/admin/product-type',
        '/admin/plugin',
        '/admin/user',
    ];
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testSuperAdminPermissions(): void
    {
        $expectedMenuItems = [
            'Home' => '/admin',
            'Products' => '/admin/product',
            'Locales' => '/admin/locale',
            'Identifier' => '/admin/identifier-type',
            'Field' => '/admin/field-type',
            'Product' => '/admin/product-type',
            'Plugin Manager' => '/admin/plugin',
            'Users' => '/admin/user',
        ];

        $this->login();

        $this->hasExpectedMenuItems($expectedMenuItems);
        $this->hasExpectedRoutePermissions($expectedMenuItems);
    }

    public function testAdminPermissions(): void
    {
        $expectedMenuItems = [
            'Home' => '/admin',
            'Products' => '/admin/product',
            'Locales' => '/admin/locale',
            'Identifier' => '/admin/identifier-type',
            'Field' => '/admin/field-type',
            'Product' => '/admin/product-type',
        ];

        $this->login(Roles::ROLE_ADMIN);

        $this->hasExpectedMenuItems($expectedMenuItems);
        $this->hasExpectedRoutePermissions($expectedMenuItems);
    }

    public function testEditorPermissions(): void
    {
        $expectedMenuItems = [
            'Home' => '/admin',
            'Products' => '/admin/product',
        ];

        $this->login(Roles::ROLE_EDITOR);

        $this->hasExpectedMenuItems($expectedMenuItems);
        $this->hasExpectedRoutePermissions($expectedMenuItems);
    }

    public function testApiUserPermissions(): void
    {
        $this->login(Roles::ROLE_API_USER);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->hasExpectedRoutePermissions([]);
    }

    public function testInactiveUserPermissions(): void
    {
        $this->login(Roles::INACTIVE);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
        $this->hasExpectedRoutePermissions([]);
    }

    /**
     * @param array<string, string> $expectedMenuItems
     */
    private function hasExpectedMenuItems(array $expectedMenuItems): void
    {
        $crawler = $this->client->getCrawler();
        $menu = $crawler->filter('#main-menu');
        $this->assertCount(1, $menu);

        $menuItems = $crawler->filter('#main-menu li.ea-sidebar-item:not(.has-submenu)');
        $this->assertCount(\count($expectedMenuItems), $menuItems);

        foreach ($expectedMenuItems as $label => $path) {
            $link = $menu->selectLink($label);

            $this->assertCount(
                1,
                $link,
                \sprintf('Menu item "%s" was not found.', $label),
            );

            $this->assertSame(
                $path,
                parse_url((string) $link->attr('href'), PHP_URL_PATH),
                \sprintf('Menu item "%s" has an unexpected URL.', $label),
            );
        }
    }

    /**
     * @param array<string, string> $expectedMenuItems
     */
    private function hasExpectedRoutePermissions(array $expectedMenuItems): void
    {
        $allowedRoutes = array_values($expectedMenuItems);

        foreach (self::ADMIN_ROUTES as $route) {
            $this->client->request('GET', $route);

            if (\in_array($route, $allowedRoutes, true)) {
                $this->assertResponseIsSuccessful(
                    \sprintf('Expected route "%s" to be accessible.', $route),
                );

                continue;
            }

            $this->assertResponseStatusCodeSame(
                Response::HTTP_FORBIDDEN,
                \sprintf('Expected route "%s" to return HTTP 403.', $route),
            );
        }
    }
}
