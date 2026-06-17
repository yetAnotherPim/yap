<?php

declare(strict_types=1);

namespace App\plugins\SamplePlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class SampleController extends AbstractController
{
    #[Route('/sample', name: 'plugin_sample')]
    public function index(): JsonResponse
    {
        // TODO: implement twig folder for plugins
        return new JsonResponse([]);
    }
}
