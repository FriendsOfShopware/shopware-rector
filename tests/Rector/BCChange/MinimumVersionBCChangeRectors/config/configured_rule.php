<?php

declare(strict_types=1);

use Frosh\Rector\Rule\BCChange\AddRequiredParameterRector;
use Frosh\Rector\Rule\BCChange\ParameterDefaultValueChangeRector;
use Frosh\Rector\Rule\BCChange\ParameterNameChangeRector;
use Frosh\Rector\Rule\BCChange\ParameterRemovalRector;
use Frosh\Rector\Rule\BCChange\ValueObject\AddRequiredParameter;
use Frosh\Rector\Rule\BCChange\ValueObject\BCChangeRuleConfiguration;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeParameterDefault;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeParameterName;
use Frosh\Rector\Rule\BCChange\ValueObject\RemoveParameter;
use Frosh\Rector\Version\ShopwareVersionRange;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../../../../../config/config_test.php');
    $versions = new ShopwareVersionRange('6.8.0', '6.9.0');

    $rectorConfig->ruleWithConfiguration(ParameterNameChangeRector::class, [new BCChangeRuleConfiguration($versions, [
        new ChangeParameterName('v6.8.0', TargetCoreClass::class, 'rename', 0, 'oldName', 'newName', []),
        new ChangeParameterName('v6.9.0', TargetCoreClass::class, 'bridgeRename', 0, 'oldName', 'newName', []),
    ])]);
    $rectorConfig->ruleWithConfiguration(ParameterRemovalRector::class, [new BCChangeRuleConfiguration($versions, [
        new RemoveParameter('v6.8.0', TargetCoreClass::class, 'remove', 1, 'obsolete'),
        new RemoveParameter('v6.8.0', TargetCoreClass::class, 'removeUsed', 1, 'obsolete'),
    ])]);
    $rectorConfig->ruleWithConfiguration(AddRequiredParameterRector::class, [new BCChangeRuleConfiguration($versions, [
        new AddRequiredParameter('v6.8.0', TargetCoreClass::class, 'required', 1, 'context', 'object'),
    ])]);
    $rectorConfig->ruleWithConfiguration(ParameterDefaultValueChangeRector::class, [new BCChangeRuleConfiguration($versions, [
        new ChangeParameterDefault('v6.8.0', TargetCoreClass::class, 'enabled', 0, 'enabled', false),
    ])]);
};
