<?php

declare(strict_types=1);

use Frosh\Rector\Rule\v65\AbstractMessageHandlerToMessageSubscriberRector;
use Frosh\Rector\Rule\v67\AddEntityNameToEntityExtension;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/v6.5/flysystem-v3.php');
    $rectorConfig->import(__DIR__ . '/v6.5/renaming.php');
    $rectorConfig->import(__DIR__ . '/v6.5/typehints.php');
    $rectorConfig->import(__DIR__ . '/symfony.php');

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
    ]);

    $rectorConfig->ruleWithConfiguration(AddEntityNameToEntityExtension::class, [
        'backwardsCompatible' => true,
    ]);

    $rectorConfig->rule(AbstractMessageHandlerToMessageSubscriberRector::class);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);
};
