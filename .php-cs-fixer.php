<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['var', 'docker', 'vendor', 'config', 'node_modules', 'public', 'storage'])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12'                => true,
        'strict_param'          => true,
        'array_syntax'          => ['syntax' => 'short'],
        'no_unused_imports'     => true,
        'use_arrow_functions'   => true,
        'no_useless_else'       => true,
        'phpdoc_separation'     => true,
        'method_argument_space' => [
            'on_multiline'                     => 'ensure_fully_multiline',
            'keep_multiple_spaces_after_comma' => false,
        ],
        'ordered_imports'             => ['sort_algorithm' => 'alpha'],
        'single_import_per_statement' => true,
        'class_attributes_separation' => [
            'elements' => ['const' => 'one', 'method' => 'one', 'property' => 'one'],
        ],
        'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true],
        'visibility_required'        => ['elements' => ['property', 'method', 'const']],
        'phpdoc_order'               => true,
        'yoda_style'                 => true,
        'binary_operator_spaces'     => [
            'default'   => 'single_space',
            'operators' => ['=>' => 'align_single_space'],
        ],
        'trailing_comma_in_multiline' => ['elements' => ['arrays']],
        'declare_strict_types'        => true,
        'concat_space'                => ['spacing' => 'one'],
    ])
    ->setFinder($finder)
;
