<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/v6.7/renaming.php');
    $rectorConfig->import(__DIR__ . '/v6.7/return-types.php');
    $rectorConfig->import(__DIR__ . '/v6.7/scheduled-task-logger.php');
    $rectorConfig->import(__DIR__ . '/v6.7/entity-extension-replacement.php');

    $rectorConfig->sets([
        SymfonySetList::COMPOSER_BASED,
    ]);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);
};
