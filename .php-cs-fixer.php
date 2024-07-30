<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['var', 'docker', 'vendor', 'config', 'node_modules', 'public', 'storage'])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'use_arrow_functions' => true,
        'no_useless_else' => true,
    ])
    ->setFinder($finder)
;
