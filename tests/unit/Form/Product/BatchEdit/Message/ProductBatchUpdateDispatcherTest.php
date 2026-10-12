<?php

declare(strict_types=1);

namespace App\Tests\unit\Form\Product\BatchEdit\Message;

use App\Form\Product\BatchEdit\Dto\BatchEditData;
use App\Form\Product\BatchEdit\Message\ProductBatchUpdateDispatcher;
use App\Form\Product\BatchEdit\Message\Transformer\ProductUpdateMessageTransformer;
use App\Messenger\ProductUpdate\Message\UpdateProductMessage;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(ProductBatchUpdateDispatcher::class)]
class ProductBatchUpdateDispatcherTest extends MockeryTestCase
{
    public function testExecuteTransformsAndDispatchesMessage(): void
    {
        $productId = 'product-123';

        $data = \Mockery::mock(BatchEditData::class);
        $message = \Mockery::mock(UpdateProductMessage::class);

        $transformer = \Mockery::mock(ProductUpdateMessageTransformer::class);
        $transformer
            ->shouldReceive('transform')
            ->once()
            ->with($data, $productId)
            ->andReturn($message);

        $messageBus = \Mockery::mock(MessageBusInterface::class);
        $messageBus
            ->shouldReceive('dispatch')
            ->once()
            ->with($message)
            ->andReturn(new Envelope($message));

        $dispatcher = new ProductBatchUpdateDispatcher(
            $transformer,
            $messageBus,
        );

        $dispatcher->dispatch($data, $productId);
    }
}
