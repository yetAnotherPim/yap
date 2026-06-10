<?php

declare(strict_types=1);

namespace App\Entity\Field\Type\Enum;

enum InputType: string
{
    case Integer = 'integer';
    case String = 'string';
    case Text = 'text';
    case Boolean = 'boolean';
    case MultiSelect = 'multiselect';
    case Select = 'select';
    case Date = 'date';
    case Time = 'time';
    case Tree = 'tree';
}
