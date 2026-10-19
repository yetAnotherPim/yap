<?php

declare(strict_types=1);

namespace App\Tests\unit\plugins\JsConsumer\Controller;

use App\plugins\JsConsumer\Consumer\MessageConsumer;
use App\plugins\JsConsumer\Controller\JsConsumerController;
use App\plugins\JsConsumer\Exception\MessageConsumptionException;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(JsConsumerController::class)]
class JsConsumerControllerTest extends TestCase
{
    private ContainerInterface $containerMock;

    /** @var MessageConsumer|MockInterface */
    private MessageConsumer $consumerMock;

    private JsConsumerController $controller;

    public function setUp(): void
    {
        parent::setUp();

        $this->containerMock = \Mockery::mock(ContainerInterface::class);
        $this->consumerMock = \Mockery::mock(MessageConsumer::class);
        $this->controller = new JsConsumerController($this->consumerMock);

        $this->containerMock
            ->shouldReceive('has')
            ->with('serializer')
            ->andReturnFalse();

        $this->controller->setContainer($this->containerMock);
    }

    public function testConsumeReturnsSuccessResponse(): void
    {
        $this->consumerMock
            ->shouldReceive('consumeOne')
            ->once();

        $response = $this->controller->consume();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(
            '{"success":true}',
            $response->getContent(),
        );
    }

    public function testConsumeReturnsErrorResponseWhenConsumptionFails(): void
    {
        $exception = new MessageConsumptionException('Consumption failed.');

        $this->consumerMock
            ->shouldReceive('consumeOne')
            ->once()
            ->andThrow($exception);

        $response = $this->controller->consume();

        $this->assertSame(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            $response->getStatusCode(),
        );

        $this->assertSame(
            '{"success":false,"errorMessage":"Consumption failed."}',
            $response->getContent(),
        );
    }
}
