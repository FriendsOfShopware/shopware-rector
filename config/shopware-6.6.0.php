<?php

declare(strict_types=1);

use Frosh\Rector\Rule\v67\AddEntityNameToEntityExtension;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/v6.6/renaming.php');
    $rectorConfig->import(__DIR__ . '/v6.6/exceptions.php');
    $rectorConfig->import(__DIR__ . '/symfony.php');

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,
    ]);

    $rectorConfig->ruleWithConfiguration(AddEntityNameToEntityExtension::class, [
        'backwardsCompatible' => true,
    ]);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);
};
