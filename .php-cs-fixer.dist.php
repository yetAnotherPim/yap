<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
    ->notPath([
        'config/bundles.php',
        'config/reference.php',
    ])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,
        'phpdoc_to_comment' => [
            'allow_before_return_statement' => false,
            'ignored_tags' => ['phpstan-var', 'phpstan-param', 'codeCoverageIgnore', 'codeCoverageIgnoreStart', 'codeCoverageIgnoreEnd'],
        ],
        'full_opening_tag' => false,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
        'no_unused_imports' => true,
        'no_superfluous_phpdoc_tags' => true,
        'linebreak_after_opening_tag' => true,
        'logical_operators' => true,
        'native_function_invocation' => [
            'include' => ['@compiler_optimized'],
            'scope' => 'namespaced',
        ],
        'declare_strict_types' => true,
    ])
    ->setFinder($finder)
;
