<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Admin;

use App\Controller\Admin\OrphanDashboardController;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(OrphanDashboardController::class)]
class OrphanDashboardControllerTest extends MockeryTestCase
{
    public function testIndexRendersEasyAdminLayout(): void
    {
        $expectedResponse = new Response();

        $controller = \Mockery::mock(OrphanDashboardController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller
            ->shouldReceive('render')
            ->once()
            ->with(
                '@EasyAdmin/layout.html.twig',
                []
            )
            ->andReturn($expectedResponse);

        $response = $controller->index();

        self::assertSame(
            $expectedResponse,
            $response
        );
    }
}
