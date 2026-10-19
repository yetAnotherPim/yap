<?php

declare(strict_types=1);

namespace App\plugins\JsConsumer\Controller;

use App\plugins\JsConsumer\Consumer\MessageConsumer;
use App\plugins\JsConsumer\Exception\MessageConsumptionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_EDITOR')]
class JsConsumerController extends AbstractController
{
    public function __construct(private readonly MessageConsumer $messageConsumer)
    {
    }

    #[Route(
        '/admin/js-consumer/consume',
        name: 'admin_js_consumer_consume',
        methods: ['POST'],
    )]
    public function consume(): JsonResponse
    {
        try {
            $this->messageConsumer->consumeOne();
        } catch (MessageConsumptionException $exception) {
            return $this->json(
                [
                    'success' => false,
                    'errorMessage' => $exception->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->json([
            'success' => true,
        ]);
    }
}
