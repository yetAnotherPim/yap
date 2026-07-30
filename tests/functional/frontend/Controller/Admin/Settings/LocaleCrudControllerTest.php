<?php

declare(strict_types=1);

namespace App\Tests\functional\frontend\Controller\Admin\Settings;

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
use App\Entity\Locale\Locale;
use App\Entity\User\User;
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
use Zenstruck\Foundry\Test\Factories;

#[CoversClass(LocaleCrudController::class)]
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
#[UsesClass(ProductTypeCrudController::class)]
#[UsesClass(ProductCrudController::class)]
#[UsesClass(ProductTypeFieldConfigurationCrudController::class)]
#[UsesClass(PluginCrudController::class)]
#[UsesClass(UserCrudController::class)]
#[UsesClass(IndexController::class)]
#[UsesClass(LoginController::class)]
class LocaleCrudControllerTest extends WebTestCase
{
    use Factories;
    use LoginTrait;

    protected KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testChangeDefaultLocale(): void
    {
        $this->login();

        $crawler = $this->client->request('GET', '/admin/locale');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('tr[data-id="en_GB"]');
        $this->assertSelectorExists('tr[data-id="de_DE"]');
        $this->assertSelectorExists('tr[data-id="es_ES"]');

        $this->assertSelectorNotExists('tr[data-id="en_GB"] td.actions div.dropdown div.dropdown-overlay ul li');
        $this->assertSelectorCount(2, 'tr[data-id="de_DE"] td.actions div.dropdown div.dropdown-overlay ul li');
        $this->assertSelectorCount(2, 'tr[data-id="es_ES"] td.actions div.dropdown div.dropdown-overlay ul li');

        $link = $crawler
            ->filter('tr[data-id="de_DE"] td.actions div.dropdown div.dropdown-overlay ul li')
            ->selectLink('Set as Default')
            ->link();
        $this->client->click($link);

        $this->assertResponseRedirects('/admin/locale');
        $this->client->followRedirect();

        $this->assertSelectorNotExists('tr[data-id="de_DE"] td.actions div.dropdown div.dropdown-overlay ul li');
        $this->assertSelectorCount(2, 'tr[data-id="en_GB"] td.actions div.dropdown div.dropdown-overlay ul li');
        $this->assertSelectorCount(2, 'tr[data-id="es_ES"] td.actions div.dropdown div.dropdown-overlay ul li');
    }
}
