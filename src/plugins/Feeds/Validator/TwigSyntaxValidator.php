<?php

declare(strict_types=1);

namespace App\plugins\Feeds\Validator;

use App\plugins\Feeds\Validator\Constraint\TwigSyntax;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Twig\Environment;
use Twig\Error\SyntaxError;
use Twig\Source;

class TwigSyntaxValidator extends ConstraintValidator
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    /**
     * @param TwigSyntax $constraint
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (!\is_string($value)) {
            return;
        }

        try {
            $tokens = $this->twig->tokenize(new Source($value, 'Feed template'));
            $this->twig->parse($tokens);
        } catch (SyntaxError $exception) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ error }}', $exception->getRawMessage())
                ->setTranslationDomain('feeds')
                ->addViolation();
        }
    }
}
