<?php

declare(strict_types=1);

namespace App\Entity\Product\Field\Type\Enum;

enum InputType: string
{
    case Section = 'section';
    case Boolean = 'boolean';
    case String = 'string';
    case Text = 'text';
    case Integer = 'integer';
    case Date = 'date';
    case DateTime = 'datetime';
    case Time = 'time';
    case Number = 'number';
    case Color = 'color';
    case Country = 'country';
    case Telephone = 'telephone';
    case Email = 'email';
    case Language = 'language';
    case Locale = 'locale';
    case TextEditor = 'texteditor';
    case Url = 'url';
}

/*
ChoiceField
    -> configure available options via config as with fieldType - Type Select
    -> implement configuration for multiselect -> convert values on save - Type Multiselect

FileField
    -> setUploadedFileNamePattern
ImageField
    -> setUploadedFileNamePattern
*/
