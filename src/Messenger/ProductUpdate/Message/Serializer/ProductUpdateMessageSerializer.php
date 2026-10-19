<?php

declare(strict_types=1);

namespace App\Messenger\ProductUpdate\Message\Serializer;

use App\Messenger\ProductUpdate\Message\ProductUpdateMessage;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Serialization\Serializer;

class ProductUpdateMessageSerializer extends Serializer
{
    public function decode(array $encodedEnvelope): Envelope
    {
        $encodedEnvelope['headers'] ??= [];
        $encodedEnvelope['headers']['type'] ??= ProductUpdateMessage::class;

        return parent::decode($encodedEnvelope);
    }

    public function getMessageType(array $encodedEnvelope): ?string
    {
        return parent::getMessageType($encodedEnvelope)
            ?? ProductUpdateMessage::class;
    }
}
