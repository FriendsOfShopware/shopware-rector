<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/v6.5/flysystem-v3.php');
    $rectorConfig->import(__DIR__ . '/v6.5/renaming.php');
    $rectorConfig->import(__DIR__ . '/v6.5/typehints.php');
    $rectorConfig->import(__DIR__ . '/v6.5/rules.php');
    $rectorConfig->import(__DIR__ . '/v6.7/entity-extension-bridge.php');

    $rectorConfig->sets([
        SymfonySetList::COMPOSER_BASED,
        SetList::PHP_74,
        SetList::PHP_80,
        SetList::PHP_81,
    ]);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);
};
