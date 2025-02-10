<?php

declare(strict_types=1);

use Frosh\Rector\Rule\v67\AddEntityNameToEntityExtension;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/v6.5/flysystem-v3.php');
    $rectorConfig->import(__DIR__ . '/v6.5/renaming.php');
    $rectorConfig->import(__DIR__ . '/v6.5/typehints.php');

    $rectorConfig->sets([
        SymfonySetList::SYMFONY_54,
        SymfonySetList::SYMFONY_60,
        SymfonySetList::SYMFONY_61,
        SymfonySetList::SYMFONY_62,
        SetList::PHP_74,
        SetList::PHP_80,
        SetList::PHP_81,
    ]);

    $rectorConfig->ruleWithConfiguration(AddEntityNameToEntityExtension::class, [
        'backwardsCompatible' => true,
    ]);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);
};
