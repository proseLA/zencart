<?php

    $finder = PhpCsFixer\Finder::create()
        ->exclude('somedir')
        ->notPath('src/Symfony/Component/Translation/Tests/fixtures/resources.php')
        ->in(__DIR__)
    ;

    $config = new PhpCsFixer\Config();
    $config->setIndent("    ");
    return $config->setRules([
        '@PHP81Migration' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        'echo_tag_syntax' => ['format' => 'short'],
        'method_argument_space' => ['on_multiline' => 'ensure_single_line'],
        'elseif' => true,
        'control_structure_braces' => true,
        'braces' => [
            'allow_single_line_closure' => false,
        ],
    ])
        ->setFinder($finder)
        ;