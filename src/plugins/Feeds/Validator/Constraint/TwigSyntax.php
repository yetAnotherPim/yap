<?php

declare(strict_types=1);

namespace App\plugins\Feeds\Validator\Constraint;

use App\plugins\Feeds\Validator\TwigSyntaxValidator;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD)]
class TwigSyntax extends Constraint
{
    public string $message = 'validation.twig_syntax';

    public function validatedBy(): string
    {
        return TwigSyntaxValidator::class;
    }
}
