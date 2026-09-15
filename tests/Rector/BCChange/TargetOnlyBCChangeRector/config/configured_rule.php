<?php

declare(strict_types=1);

use Frosh\Rector\Rule\BCChange\BCChangeRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../../../../../config/config_test.php');
    $rectorConfig->ruleWithConfiguration(BCChangeRector::class, [
        'minimumVersion' => '6.8.0',
        'targetVersion' => '6.9.0',
        'changes' => [
            [
                'version' => 'v6.8.0',
                'kind' => BCChangeRector::RENAME_PARAMETER,
                'class' => TargetCoreClass::class,
                'method' => 'rename',
                'position' => 0,
                'parameter' => 'oldName',
                'newName' => 'newName',
                'parametersBefore' => [],
            ],
            [
                'version' => 'v6.8.0',
                'kind' => BCChangeRector::REMOVE_PARAMETER,
                'class' => TargetCoreClass::class,
                'method' => 'remove',
                'position' => 1,
                'parameter' => 'obsolete',
            ],
            [
                'version' => 'v6.8.0',
                'kind' => BCChangeRector::REMOVE_PARAMETER,
                'class' => TargetCoreClass::class,
                'method' => 'removeUsed',
                'position' => 1,
                'parameter' => 'obsolete',
            ],
            [
                'version' => 'v6.8.0',
                'kind' => BCChangeRector::ADD_REQUIRED_PARAMETER,
                'class' => TargetCoreClass::class,
                'method' => 'required',
                'position' => 1,
                'parameter' => 'context',
                'type' => 'object',
            ],
            [
                'version' => 'v6.9.0',
                'kind' => BCChangeRector::RENAME_PARAMETER,
                'class' => TargetCoreClass::class,
                'method' => 'bridgeRename',
                'position' => 0,
                'parameter' => 'oldName',
                'newName' => 'newName',
                'parametersBefore' => [],
            ],
        ],
    ]);
};
