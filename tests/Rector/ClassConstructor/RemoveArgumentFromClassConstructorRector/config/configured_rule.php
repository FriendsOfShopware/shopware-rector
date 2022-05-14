<?php

declare(strict_types=1);

use Frosh\Rector\Rule\ClassConstructor\RemoveArgumentFromClassConstruct;
use Frosh\Rector\Rule\ClassConstructor\RemoveArgumentFromClassConstructRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../../../../../config/config_test.php');
    $rectorConfig->ruleWithConfiguration(
        RemoveArgumentFromClassConstructRector::class,
        [
            new RemoveArgumentFromClassConstruct('SomeExampleClass', 0),
            new RemoveArgumentFromClassConstruct('SomeOtherExampleClass', 1),
            new RemoveArgumentFromClassConstruct('SomeMoreAdvancedExampleClass', 1),
        ]
    );
};
