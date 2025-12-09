<?php

declare(strict_types=1);

use Frosh\Rector\Rule\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use Frosh\Rector\Rule\Transform\ValueObject\PropertyFetchToMethodCall;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../../../../../config/config_test.php');
    $rectorConfig->ruleWithConfiguration(
        PropertyFetchToMethodCallRector::class,
        [new PropertyFetchToMethodCall(
            'Shopware\Core\Content\Flow\Dispatching\FlowState',
            'sequenceId',
            'getSequenceId',
            null,
        )],
    );
};
