<?php

declare(strict_types=1);

use Frosh\Rector\Rule\BCChange\FutureCompatibleBCChangeRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../../../../../config/config_test.php');
    $rectorConfig->ruleWithConfiguration(FutureCompatibleBCChangeRector::class, [
        [
            'kind' => FutureCompatibleBCChangeRector::ADD_OPTIONAL_PARAMETER,
            'class' => CoreClass::class,
            'method' => 'load',
            'position' => 1,
            'parameter' => 'fresh',
            'type' => '?' . DateTimeInterface::class,
            'default' => null,
        ],
        [
            'kind' => FutureCompatibleBCChangeRector::WIDEN_PARAMETER_TYPE,
            'class' => CoreClass::class,
            'method' => 'load',
            'parameter' => 'id',
            'currentType' => 'string',
            'type' => 'int|string',
        ],
        [
            'kind' => FutureCompatibleBCChangeRector::NARROW_RETURN_TYPE,
            'class' => CoreClass::class,
            'method' => 'load',
            'currentType' => 'object',
            'type' => 'static',
        ],
        [
            'kind' => FutureCompatibleBCChangeRector::EXPLICIT_CURRENT_DEFAULT,
            'class' => CoreClass::class,
            'method' => 'enabled',
            'position' => 0,
            'parameter' => 'enabled',
            'default' => false,
        ],
    ]);
};
