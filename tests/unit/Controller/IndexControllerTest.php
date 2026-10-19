<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller;

use App\Controller\IndexController;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;

#[CoversClass(IndexController::class)]
class IndexControllerTest extends MockeryTestCase
{
    private IndexController $controller;

    public function setUp(): void
    {
        $this->controller = new IndexController();

        parent::setUp();
    }

    public function testConfigureUserMenuCreatesLocaleMenu(): void
    {
        $containerMock = \Mockery::mock(ContainerInterface::class);
        $routerMock = \Mockery::mock(RouterInterface::class);

        $containerMock
            ->shouldReceive('get')
            ->once()
            ->with('router')
            ->andReturn($routerMock);

        $routerMock
            ->shouldReceive('generate')
            ->once()
            ->with('admin', [], 1)
            ->andReturn('/admin');

        $this->controller->setContainer($containerMock);

        $response = $this->controller->index();

        $this->assertInstanceOf(RedirectResponse::class, $response);
    }
}
