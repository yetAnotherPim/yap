<?php

declare(strict_types=1);

namespace App\Messenger\ProductUpdate\Message\Enum;

enum FieldTypeIdentifierType: string
{
    case ID = 'id';
    case CODE = 'code';
}
