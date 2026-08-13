<?php declare(strict_types=1);

/*
 * This document has been generated with
 * https://mlocati.github.io/php-cs-fixer-configurator/#version:3.95.18|configurator
 * you can change this configuration by importing this file.
 */
return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PhpCsFixer' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP82Migration' => true,
        '@PHP80Migration:risky' => true,
        '@PER-CS2.0' => true,
        '@PER-CS2.0:risky' => true,
        // Ensure there is no code on the same line as the PHP open tag and it is followed by a blank line.
        'blank_line_after_opening_tag' => false,
        // Using `isset($var) &&` multiple times should be done in one call.
        'combine_consecutive_issets' => false,
        // Calling `unset` on multiple items should be done in one call.
        'combine_consecutive_unsets' => false,
        // Spaces should be properly placed in a function declaration.
        'function_declaration' => ['closure_fn_spacing' => 'one'],
        // Pre- or post-increment and decrement operators should be used if possible.
        'increment_style' => ['style' => 'post'],
        // Ensure there is no code on the same line as the PHP open tag.
        'linebreak_after_opening_tag' => false,
        // Add leading `\` before function invocation to speed up resolving.
        'native_function_invocation' => false,
        // All items of the given PHPDoc tags must be either left-aligned or (by default) aligned vertically.
        'phpdoc_align' => ['align' => 'left'],
        // Throwing exception must be done in single line.
        'single_line_throw' => false,
        // Comparisons should be strict.
        'strict_comparison' => true,
        // Functions should be used with `$strict` param set to `true`.
        'strict_param' => true,
        // Anonymous functions with one-liner return statement must use arrow functions.
        'use_arrow_functions' => false,
        // Write conditions in Yoda style (`true`), non-Yoda style (`['equal' => false, 'identical' => false, 'less_and_greater' => false]`) or ignore those conditions (`null`) based on configuration.
        'yoda_style' => false,
        // Docblocks should only be used on structural elements.
        'phpdoc_to_comment' => ['ignored_tags' => ['deprecated', 'var']],
    ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->exclude('vendor')
        ->exclude('stubs')
        ->in(__DIR__)
    )
;
