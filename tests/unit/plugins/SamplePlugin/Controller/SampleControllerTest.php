<?php

declare(strict_types=1);

namespace App\Tests\unit\plugins\SamplePlugin\Controller;

use App\plugins\SamplePlugin\Controller\SampleController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

#[CoversClass(SampleController::class)]
class SampleControllerTest extends TestCase
{
    public function testIndexReturnsEmptyJsonResponse(): void
    {
        $controller = new SampleController();

        $response = $controller->index();

        /* @phpstan-ignore-next-line */
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('[]', $response->getContent());
    }
}
