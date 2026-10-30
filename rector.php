<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;

// CI runs this as a dry run, so a change Rector would make fails the build. Run `composer rector:fix` to apply it.
return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    // The PHP version comes from composer.json's "php" constraint, so the sets follow the minimum we support.
    ->withPhpSets()
    ->withPreparedSets(deadCode: true, codeQuality: true, typeDeclarations: true)
    ->withAttributesSets(phpunit: true)
    ->withSkip([
        // Pure helpers stay static, so it's clear they don't touch the instance.
        LocallyCalledStaticMethodToNonStaticRector::class,
        // 2 ** n is a float once it overflows an int, and random_int() needs an int.
        RecastingRemovalRector::class => [__DIR__ . '/src/RetryPolicy.php'],
    ]);
