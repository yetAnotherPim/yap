<?php

declare(strict_types=1);

namespace App\Tests\integration\Controller\Admin\Login;

use App\Controller\Admin\Login\LoginController;
use App\Controller\Admin\Products\IdentifierCrudController;
use App\Controller\Admin\Products\ProductCrudController;
use App\Controller\Admin\Settings\FieldTypeCrudController;
use App\Controller\Admin\Settings\IdentifierTypeCrudController;
use App\Controller\Admin\Settings\LocaleCrudController;
use App\Controller\Admin\Settings\PluginCrudController;
use App\Controller\Admin\Settings\ProductTypeCrudController;
use App\Controller\Admin\Settings\ProductTypeFieldConfigurationCrudController;
use App\Entity\User\User;
use App\PluginManager\Cache\CacheClearer;
use App\PluginManager\PluginManager;
use App\Repository\IdentifierTypeRepository;
use App\Repository\PluginRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[CoversClass(LoginController::class)]
#[CoversClass(User::class)]
#[CoversClass(UserRepository::class)]
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
class LoginControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $this->assertInstanceOf(EntityManagerInterface::class, $em);

        $userRepository = $em->getRepository(User::class);

        foreach ($userRepository->findAll() as $user) {
            $em->remove($user);
        }

        $em->flush();

        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get('security.user_password_hasher');

        $user = new User()->setEmail('email@example.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));

        $em->persist($user);
        $em->flush();
    }

    public function testLogin(): void
    {
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
            '_username' => 'email@example.com',
            '_password' => 'bad-password',
        ]);

        self::assertResponseRedirects('/admin/login');
        $this->client->followRedirect();

        self::assertSelectorTextContains('.alert-danger', 'Invalid credentials.');

        $this->client->submitForm('Sign in', [
            '_username' => 'email@example.com',
            '_password' => 'password',
        ]);

        self::assertResponseRedirects('/');
        $this->client->followRedirect();

        self::assertSelectorNotExists('.alert-danger');
    }
}
