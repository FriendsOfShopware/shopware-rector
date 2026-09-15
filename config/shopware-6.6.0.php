<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/v6.6/renaming.php');
    $rectorConfig->import(__DIR__ . '/v6.6/exceptions.php');
    $rectorConfig->import(__DIR__ . '/v6.7/entity-extension-bridge.php');

    $rectorConfig->sets([
        SymfonySetList::COMPOSER_BASED,
        LevelSetList::UP_TO_PHP_82,
    ]);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);
};
