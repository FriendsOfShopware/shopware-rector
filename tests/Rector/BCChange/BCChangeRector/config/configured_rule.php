<?php

declare(strict_types=1);

use Frosh\Rector\Rule\BCChange\BCChangeRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../../../../../config/config_test.php');
    $rectorConfig->ruleWithConfiguration(BCChangeRector::class, [
        'minimumVersion' => '6.7.0',
        'targetVersion' => '6.8.0',
        'changes' => [
            [
                'version' => 'v6.8.0',
                'kind' => BCChangeRector::ADD_OPTIONAL_PARAMETER,
                'class' => CoreClass::class,
                'method' => 'load',
                'position' => 1,
                'parameter' => 'fresh',
                'type' => '?' . DateTimeInterface::class,
                'default' => null,
            ],
            [
                'version' => 'v6.8.0',
                'kind' => BCChangeRector::WIDEN_PARAMETER_TYPE,
                'class' => CoreClass::class,
                'method' => 'load',
                'parameter' => 'id',
                'currentType' => 'string',
                'type' => 'int|string',
            ],
            [
                'version' => 'v6.8.0',
                'kind' => BCChangeRector::NARROW_RETURN_TYPE,
                'class' => CoreClass::class,
                'method' => 'load',
                'currentType' => 'object',
                'type' => 'static',
            ],
            [
                'version' => 'v6.8.0',
                'kind' => BCChangeRector::EXPLICIT_CURRENT_DEFAULT,
                'class' => CoreClass::class,
                'method' => 'enabled',
                'position' => 0,
                'parameter' => 'enabled',
                'default' => false,
            ],
            [
                'version' => 'v6.8.0',
                'kind' => BCChangeRector::RENAME_PARAMETER,
                'class' => CoreClass::class,
                'method' => 'rename',
                'position' => 2,
                'parameter' => 'oldName',
                'newName' => 'newName',
                'parametersBefore' => [
                    ['name' => 'required', 'hasDefault' => false],
                    ['name' => 'optional', 'hasDefault' => true, 'default' => false],
                ],
            ],
            [
                'version' => 'v7.0.0',
                'kind' => BCChangeRector::RENAME_PARAMETER,
                'class' => CoreClass::class,
                'method' => 'future',
                'position' => 0,
                'parameter' => 'oldName',
                'newName' => 'newName',
                'parametersBefore' => [],
            ],
        ],
    ]);
};
