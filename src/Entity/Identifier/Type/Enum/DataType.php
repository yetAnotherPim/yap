<?php

declare(strict_types=1);

namespace App\Entity\Identifier\Type\Enum;

enum DataType: string
{
    case Integer = 'integer';
    case String = 'string';
}
