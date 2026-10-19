<?php

declare(strict_types=1);

namespace App\plugins\Scheduler\Controller\Admin;

use App\plugins\Scheduler\Provider\TaskProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TasksController extends AbstractController
{
    public function __construct(private readonly TaskProvider $taskProvider)
    {
    }

    #[Route(
        '/admin/scheduler/tasks',
        name: 'yap_scheduler_tasks',
        methods: ['GET'],
    )]
    public function index(): Response
    {
        return $this->render(
            '@Scheduler/tasks/index.html.twig',
            [
                'tasks' => $this->taskProvider->getTasks(),
            ],
        );
    }
}
