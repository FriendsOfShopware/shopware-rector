<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/v6.6/renaming.php');
    $rectorConfig->import(__DIR__ . '/v6.6/exceptions.php');

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);
};
