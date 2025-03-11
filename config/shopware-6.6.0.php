<?php

declare(strict_types=1);

use Frosh\Rector\Rule\v67\AddEntityNameToEntityExtension;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/v6.6/renaming.php');
    $rectorConfig->import(__DIR__ . '/v6.6/exceptions.php');

    $rectorConfig->sets([
        SymfonySetList::SYMFONY_63,
        SymfonySetList::SYMFONY_64,
        SymfonySetList::SYMFONY_70,
        LevelSetList::UP_TO_PHP_82,
    ]);

    $rectorConfig->ruleWithConfiguration(AddEntityNameToEntityExtension::class, [
        'backwardsCompatible' => true,
    ]);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);
};
