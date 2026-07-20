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
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('[]', $response->getContent());
    }
}
