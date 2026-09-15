#!/usr/bin/env php
<?php

declare(strict_types=1);

use Frosh\Rector\Generator\BCChangeConfigGenerator;

require dirname(__DIR__) . '/vendor/autoload.php';

if ($argc !== 4) {
    fwrite(\STDERR, "Usage: bin/generate-bc-change-config.php <shopware-autoload.php> <version> <output.php>\n");
    exit(1);
}

[, $autoloadFile, $version, $outputFile] = $argv;
require $autoloadFile;

$classMapFile = dirname($autoloadFile) . '/composer/autoload_classmap.php';
if (!is_file($classMapFile)) {
    throw new RuntimeException(sprintf('Composer class map "%s" does not exist.', $classMapFile));
}

/** @var array<class-string, string> $classMap */
$classMap = require $classMapFile;
$shopwareSource = dirname($autoloadFile, 2) . '/src/';
$supportedAttributes = [
    'NewOptionalParameter',
    'NewRequiredParameter',
    'ParameterDefaultValueChange',
    'ParameterNameChange',
    'ParameterRemoval',
    'ParameterTypeWidening',
    'ReturnTypeNarrowing',
];
$classes = array_keys(array_filter(
    $classMap,
    static function (string $file, string $class) use ($shopwareSource, $supportedAttributes, $version): bool {
        $resolvedFile = realpath($file);

        if (!str_starts_with($class, 'Shopware\\') || $resolvedFile === false || !str_starts_with($resolvedFile, $shopwareSource)) {
            return false;
        }

        $source = file_get_contents($resolvedFile);

        if ($source === false || !str_contains($source, $version)) {
            return false;
        }

        foreach ($supportedAttributes as $attribute) {
            if (str_contains($source, $attribute)) {
                return true;
            }
        }

        return false;
    },
    \ARRAY_FILTER_USE_BOTH,
));

$generator = new BCChangeConfigGenerator();
$existingChanges = is_file($outputFile) ? require $outputFile : [];
if (!is_array($existingChanges)) {
    throw new RuntimeException(sprintf('Existing manifest "%s" must return an array.', $outputFile));
}

$changes = $generator->replaceVersion($existingChanges, $generator->collect($classes, $version), $version);
$configuration = $generator->render($changes);

if (file_put_contents($outputFile, $configuration) === false) {
    throw new RuntimeException(sprintf('Could not write generated configuration "%s".', $outputFile));
}
