<?php

declare(strict_types=1);

use Frosh\Rector\Rule\BCChange\AddOptionalParameterRector;
use Frosh\Rector\Rule\BCChange\ParameterDefaultValueChangeRector;
use Frosh\Rector\Rule\BCChange\ParameterNameChangeRector;
use Frosh\Rector\Rule\BCChange\ParameterTypeWideningRector;
use Frosh\Rector\Rule\BCChange\ReturnTypeNarrowingRector;
use Frosh\Rector\Rule\BCChange\ValueObject\AddOptionalParameter;
use Frosh\Rector\Rule\BCChange\ValueObject\BCChangeRuleConfiguration;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeParameterDefault;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeParameterName;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeParameterType;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeReturnType;
use Frosh\Rector\Version\ShopwareVersionRange;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../../../../../config/config_test.php');
    $versions = new ShopwareVersionRange('6.7.0', '6.8.0');

    $rectorConfig->ruleWithConfiguration(AddOptionalParameterRector::class, [new BCChangeRuleConfiguration($versions, [
        new AddOptionalParameter('v6.8.0', CoreClass::class, 'load', 1, 'fresh', '?' . DateTimeInterface::class, null),
    ])]);
    $rectorConfig->ruleWithConfiguration(ParameterTypeWideningRector::class, [new BCChangeRuleConfiguration($versions, [
        new ChangeParameterType('v6.8.0', CoreClass::class, 'load', 'id', 'string', 'int|string'),
    ])]);
    $rectorConfig->ruleWithConfiguration(ReturnTypeNarrowingRector::class, [new BCChangeRuleConfiguration($versions, [
        new ChangeReturnType('v6.8.0', CoreClass::class, 'load', 'object', 'static'),
    ])]);
    $rectorConfig->ruleWithConfiguration(ParameterDefaultValueChangeRector::class, [new BCChangeRuleConfiguration($versions, [
        new ChangeParameterDefault('v6.8.0', CoreClass::class, 'enabled', 0, 'enabled', false),
    ])]);
    $rectorConfig->ruleWithConfiguration(ParameterNameChangeRector::class, [new BCChangeRuleConfiguration($versions, [
        new ChangeParameterName('v6.8.0', CoreClass::class, 'rename', 2, 'oldName', 'newName', [
            ['name' => 'required', 'hasDefault' => false],
            ['name' => 'optional', 'hasDefault' => true, 'default' => false],
        ]),
        new ChangeParameterName('v7.0.0', CoreClass::class, 'future', 0, 'oldName', 'newName', []),
    ])]);
};
