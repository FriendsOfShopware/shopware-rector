#!/usr/bin/env php
<?php

declare(strict_types=1);

use Frosh\Rector\Generator\BCChangeConfigGenerator;

require dirname(__DIR__) . '/vendor/autoload.php';

if (!isset($argv) || count($argv) !== 4) {
    fwrite(\STDERR, "Usage: bin/generate-bc-change-config.php <shopware-autoload.php> <version> <output.php>\n");
    exit(1);
}

[, $autoloadFile, $version, $outputFile] = $argv;
require $autoloadFile;

$shopwareSource = dirname($autoloadFile, 2) . '/src/';
$supportedAttributes = ['ClassMoved', 'NewOptionalParameter', 'ParameterDefaultValueChange', 'ParameterTypeWidening', 'ReturnTypeNarrowing'];
$classes = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($shopwareSource, FilesystemIterator::SKIP_DOTS)) as $file) {
    if ($file->getExtension() !== 'php') {
        continue;
    }

    $source = file_get_contents($file->getPathname());
    if ($source === false) {
        throw new RuntimeException(sprintf('Could not read Shopware source "%s".', $file->getPathname()));
    }

    foreach ($supportedAttributes as $attribute) {
        if (str_contains($source, $attribute)) {
            // Discover PSR-4 classes even when Composer's class map is not optimized.
            $class = 'Shopware\\' . str_replace('/', '\\', substr($file->getPathname(), strlen($shopwareSource), -4));
            if (!class_exists($class) && !interface_exists($class) && !trait_exists($class)) {
                throw new RuntimeException(sprintf('Cannot autoload Shopware source class "%s".', $class));
            }
            $classes[] = $class;
            break;
        }
    }
}

$generator = new BCChangeConfigGenerator();
$configuration = $generator->render($generator->collect($classes, $version), $generator->collectClassRenames($classes, $version));

if (file_put_contents($outputFile, $configuration) === false) {
    throw new RuntimeException(sprintf('Could not write generated configuration "%s".', $outputFile));
}
