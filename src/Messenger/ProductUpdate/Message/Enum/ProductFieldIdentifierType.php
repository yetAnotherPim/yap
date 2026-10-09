<?php

declare(strict_types=1);

namespace App\Messenger\ProductUpdate\Message\Enum;

enum ProductFieldIdentifierType: string
{
    case ID = 'id';
    case CODE = 'code';
}
