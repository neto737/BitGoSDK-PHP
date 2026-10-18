<?php

$finder = PhpCsFixer\Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/examples']);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        // Constant visibility needs PHP 7.1. Add 'const' back once the minimum PHP is raised to 7.4.
        'modifier_keywords' => ['elements' => ['method', 'property']],
    ])
    ->setFinder($finder);
