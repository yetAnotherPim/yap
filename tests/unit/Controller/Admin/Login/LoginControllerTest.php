<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin\Login;

use App\Controller\Admin\Login\LoginController;
use App\Repository\LocaleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Container\ContainerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

#[CoversClass(LoginController::class)]
class LoginControllerTest extends MockeryTestCase
{
    public function testLogin(): void
    {
        $localeRepositoryMock = \Mockery::mock(LocaleRepository::class);
        $authenticationUtilsMock = \Mockery::mock(AuthenticationUtils::class);
        $containerMock = \Mockery::mock(ContainerInterface::class);
        $twigMock = \Mockery::mock(Environment::class);

        $localeCollection = new ArrayCollection();

        $authenticationUtilsMock
            ->shouldReceive('getLastAuthenticationError')
            ->once()
            ->andReturn(null);

        $authenticationUtilsMock
            ->shouldReceive('getLastUsername')
            ->once()
            ->andReturn('admin');

        $localeRepositoryMock
            ->shouldReceive('getAvailableLocales')
            ->once()
            ->andReturn($localeCollection);

        $containerMock
            ->shouldReceive('has')
            ->once()
            ->with('twig')
            ->andReturn(true);

        $containerMock
            ->shouldReceive('get')
            ->once()
            ->with('twig')
            ->andReturn($twigMock);

        $twigMock
            ->shouldReceive('render')
            ->once()
            ->with('login/login.html.twig', [
                'locales' => $localeCollection,
                'last_username' => 'admin',
                'error' => null,
            ]);

        $controller = new LoginController($localeRepositoryMock);
        $controller->setContainer($containerMock);

        $controller->login($authenticationUtilsMock);
    }

    public function testLogout(): void
    {
        $localeRepositoryMock = \Mockery::mock(LocaleRepository::class);
        $controller = new LoginController($localeRepositoryMock);

        $this->expectException(\LogicException::class);

        $controller->logout();
    }
}
