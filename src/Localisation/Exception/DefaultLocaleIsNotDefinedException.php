<?php

declare(strict_types=1);

namespace App\Localisation\Exception;

class DefaultLocaleIsNotDefinedException extends \Exception
{
    public function __construct(string $message = 'Default locale is not configured!', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
