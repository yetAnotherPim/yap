<?php

declare(strict_types=1);

namespace App\Tests\functional\frontend\Controller\Admin\Login;

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
use App\Tests\TestData\Factory\Locale\LocaleFactory;
use App\Tests\TestData\Factory\User\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Test\Factories;

#[CoversClass(LoginController::class)]
#[CoversClass(User::class)]
#[CoversClass(UserRepository::class)]
#[CoversClass(LocaleListener::class)]
#[CoversClass(LoginSuccessListener::class)]
#[CoversClass(DefaultLocaleIsNotDefinedException::class)]
#[CoversClass(Locale::class)]
#[CoversClass(LocaleRepository::class)]
#[UsesClass(PluginManager::class)]
#[UsesClass(PluginRepository::class)]
#[UsesClass(CacheClearer::class)]
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
class LoginControllerTest extends WebTestCase
{
    use Factories;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManagerInterface::class, $em);

        $passwordHasher = $container->get('security.user_password_hasher');
        $this->assertInstanceOf(UserPasswordHasherInterface::class, $passwordHasher);

        UserFactory::createOne(
            [
                'username' => 'username',
                'firstName' => 'first name',
                'lastName' => 'last name',
                'email' => 'email@example.com',
                'password' => $passwordHasher->hashPassword(new User(), 'password'),
            ]
        );
    }

    public function testLogin(): void
    {
        LocaleFactory::createOne(
            ['id' => 'en_GB', 'default' => true]
        );

        $this->client->request('GET', '/admin/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'doesNotExist@example.com',
            '_password' => 'password',
        ]);

        self::assertResponseRedirects('/admin/login');
        $this->client->followRedirect();

        self::assertSelectorTextContains('.alert-danger', 'Invalid credentials.');

        $this->client->request('GET', '/admin/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'username',
            '_password' => 'bad-password',
        ]);

        self::assertResponseRedirects('/admin/login');
        $this->client->followRedirect();

        self::assertSelectorTextContains('.alert-danger', 'Invalid credentials.');

        $this->client->submitForm('Sign in', [
            '_username' => 'username',
            '_password' => 'password',
        ]);

        self::assertResponseRedirects('/admin');
        $this->client->followRedirect();

        self::assertSelectorNotExists('.alert-danger');
    }

    public function testLoginWorksIfDefaultLocaleIsNotConfiguredAndErrorIsShown(): void
    {
        $this->client->request('GET', '/admin/login');

        $this->client->submitForm('Sign in', [
            '_username' => 'username',
            '_password' => 'password',
        ]);

        self::assertResponseRedirects('/admin');
        $this->client->followRedirect();

        self::assertSelectorTextContains('.alert-danger', 'Default locale is not configured!');
    }
}
