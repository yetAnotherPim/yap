<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Api\Auth;

use App\Controller\Api\Auth\LoginController;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LoginController::class)]
class LoginControllerTest extends MockeryTestCase
{
    public function testLogin(): void
    {
        $controller = new LoginController();

        $this->expectException(\LogicException::class);

        $controller->login();
    }
}
