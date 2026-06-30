<?php

declare(strict_types=1);

namespace App\Tests\unit\Controller\Api;

use App\Controller\Api\PluginWorkflowController;
use App\PluginManager\Event\Enum\Action;
use App\PluginManager\Event\WorkflowEvent\Exception\PluginWorkflowException;
use App\PluginManager\PluginManager;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(PluginWorkflowController::class)]
#[UsesClass(Action::class)]
#[UsesClass(PluginWorkflowException::class)]
class PluginWorkflowControllerTest extends MockeryTestCase
{
    /**
     * @throws \JsonException
     */
    public function testPerformWorkflowReturnsAcceptedOnSuccess(): void
    {
        $pluginManager = \Mockery::mock(PluginManager::class);
        $pluginManager
            ->shouldReceive('performWorkflow')
            ->once()
            ->with(Action::ACTIVATE, 'SamplePlugin')
            ->andReturnTrue();

        $controller = new PluginWorkflowController($pluginManager);

        $response = $controller->performWorkflow('activate', 'SamplePlugin');

        self::assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
        self::assertSame(['success' => true], $this->decode($response->getContent()));
    }

    /**
     * @throws \JsonException
     */
    public function testPerformWorkflowReturnsBadRequestForInvalidAction(): void
    {
        $pluginManager = \Mockery::mock(PluginManager::class);
        $pluginManager
            ->shouldNotReceive('performWorkflow');

        $controller = new PluginWorkflowController($pluginManager);

        $response = $controller->performWorkflow('invalid', 'SamplePlugin');

        self::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertSame(
            [
                'success' => false,
                'errorMessage' => 'Invalid action given',
            ],
            $this->decode($response->getContent())
        );
    }

    /**
     * @throws \JsonException
     */
    public function testPerformWorkflowReturnsUnprocessableEntityOnWorkflowException(): void
    {
        $pluginManager = \Mockery::mock(PluginManager::class);
        $pluginManager
            ->shouldReceive('performWorkflow')
            ->once()
            ->with(Action::UPDATE, 'SamplePlugin')
            ->andThrow(new PluginWorkflowException(Action::UPDATE, 'SamplePlugin', 'Plugin cannot be updated'));

        $controller = new PluginWorkflowController($pluginManager);

        $response = $controller->performWorkflow('update', 'SamplePlugin');

        self::assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        self::assertSame(
            [
                'success' => false,
                'errorMessage' => 'Plugin cannot be updated',
            ],
            $this->decode($response->getContent())
        );
    }

    /**
     * @throws \JsonException
     */
    public function testPerformWorkflowReturnsPreconditionFailedWhenPluginNotFound(): void
    {
        $pluginManager = \Mockery::mock(PluginManager::class);
        $pluginManager
            ->shouldReceive('performWorkflow')
            ->once()
            ->with(Action::DEACTIVATE, 'UnknownPlugin')
            ->andReturnFalse();

        $controller = new PluginWorkflowController($pluginManager);

        $response = $controller->performWorkflow('deactivate', 'UnknownPlugin');

        self::assertSame(Response::HTTP_PRECONDITION_FAILED, $response->getStatusCode());
        self::assertSame(
            [
                'success' => false,
                'errorMessage' => 'Plugin invalid or not found',
            ],
            $this->decode($response->getContent())
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(string|false $content): array
    {
        self::assertIsString($content);

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }
}
