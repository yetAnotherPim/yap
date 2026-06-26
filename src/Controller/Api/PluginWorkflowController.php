<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\PluginManager\Event\Enum\Action;
use App\PluginManager\Event\WorkflowEvent\Exception\PluginWorkflowException;
use App\PluginManager\PluginManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class PluginWorkflowController extends AbstractController
{
    public function __construct(private readonly PluginManager $pluginManager)
    {
    }

    #[Route(
        name: 'perform_plugin_workflow',
        path: 'api/plugin/{action}/{name}',
        methods: ['POST']
    )]
    public function performWorkflow(string $action, string $name): JsonResponse
    {
        $action = Action::tryFromName($action);

        if (null === $action) {
            return new JsonResponse(
                [
                    'success' => false,
                    'errorMessage' => 'Invalid action given',
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $result = $this->pluginManager->performWorkflow($action, $name);
        } catch (PluginWorkflowException $exception) {
            return new JsonResponse(
                [
                    'success' => false,
                    'errorMessage' => $exception->getMessage(),
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if (false === $result) {
            return new JsonResponse(
                [
                    'success' => false,
                    'errorMessage' => 'Plugin invalid or not found',
                ],
                Response::HTTP_PRECONDITION_FAILED
            );
        }

        return new JsonResponse(['success' => true], Response::HTTP_ACCEPTED);
    }
}
