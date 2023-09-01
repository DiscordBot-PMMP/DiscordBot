<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->notPath('Libs/');

return (new PhpCsFixer\Config)
    ->setRiskyAllowed(true)
    ->setRules([
        'align_multiline_comment' => [
            'comment_type' => 'phpdocs_only'
        ],
        'array_indentation' => true,
        'array_syntax' => [
            'syntax' => 'short'
        ],
        'binary_operator_spaces' => [
            'default' => 'single_space'
        ],
        'blank_line_after_namespace' => true,
        'cast_spaces' => [
            'space' => 'none'
        ],
        'concat_space' => [
            'spacing' => 'one'
        ],
        'elseif' => true,
        'fully_qualified_strict_types' => true,
        'global_namespace_import' => [
            'import_constants' => true,
            'import_functions' => true,
            'import_classes' => null,
        ],
        'header_comment' => [
            'comment_type' => 'comment',
            'header' => <<<BODY
DiscordBot, PocketMine-MP Plugin.

Licensed under the Open Software License version 3.0 (OSL-3.0)
Copyright (C) 2020-present JaxkDev

Discord :: JaxkDev
Email   :: JaxkDev@gmail.com
BODY,
            'location' => 'after_open'
        ],
        'indentation_type' => true,
        'logical_operators' => true,
        'native_constant_invocation' => [
            'scope' => 'namespaced'
        ],
        'native_function_invocation' => [
            'scope' => 'namespaced',
            'include' => ['@all'],
        ],
        'new_with_braces' => [
            'named_class' => true,
            'anonymous_class' => false,
        ],
        'no_closing_tag' => true,
        'no_empty_phpdoc' => true,
        'no_extra_blank_lines' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
        ],
        'no_trailing_whitespace' => true,
        'no_trailing_whitespace_in_comment' => true,
        'no_whitespace_in_blank_line' => true,
        'no_unused_imports' => true,
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
            'sort_algorithm' => 'alpha'
        ],
        'phpdoc_align' => [
            'align' => 'vertical',
            'tags' => [
                'param',
            ]
        ],
        'phpdoc_line_span' => [
            'property' => 'single',
            'method' => null,
            'const' => null
        ],
        'phpdoc_trim' => true,
        'phpdoc_trim_consecutive_blank_line_separation' => true,
        'single_import_per_statement' => true,
        'strict_param' => true,
        'unary_operator_spaces' => true,
    ])
    ->setFinder($finder);
