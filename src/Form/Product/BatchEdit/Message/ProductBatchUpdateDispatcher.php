<?php

declare(strict_types=1);

namespace App\Form\Product\BatchEdit\Message;

use App\Form\Product\BatchEdit\Dto\BatchEditData;
use App\Form\Product\BatchEdit\Message\Transformer\ProductUpdateMessageTransformer;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductBatchUpdateDispatcher
{
    public function __construct(
        private readonly ProductUpdateMessageTransformer $transformer,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function dispatch(BatchEditData $data, string $productId): void
    {
        $message = $this->transformer->transform($data, $productId);

        $this->messageBus->dispatch($message);
    }
}
