<?php
return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers([
        // symfony
        'array_element_white_space_after_comma',
        'duplicate_semicolon',
//        'empty_return', do not use this, it create a conflict with __get
        'extra_empty_lines',
        'function_typehint_space',
        'join_function',
        'multiline_array_trailing_comma',
        'new_with_braces',
        'no_blank_lines_after_class_opening',
        'no_empty_lines_after_phpdocs',
        'object_operator',
        'operators_spaces',
        'phpdoc_scalar',
        'self_accessor',
        'single_array_no_trailing_comma',
        'single_blank_line_before_namespace',
        'single_quote',
        'spaces_before_semicolon',
        'unused_use',
        'whitespacy_lines',
        // contrib
        'concat_with_spaces',
        'logical_not_operators_with_successor_space',
        'newline_after_open_tag',
        'ordered_use',
        'short_array_syntax',
    ])
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()->in(__DIR__ . '/sources')
    )
;
