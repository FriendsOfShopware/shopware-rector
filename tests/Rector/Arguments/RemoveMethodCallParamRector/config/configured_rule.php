<?php

declare(strict_types=1);

use Frosh\Rector\Rule\Arguments\Rector\MethodCall\RemoveMethodCallParamRector;
use Frosh\Rector\Rule\Arguments\ValueObject\RemoveMethodCallParam;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RemoveMethodCallParamRector::class, [
        new RemoveMethodCallParam('Caller', 'process', 1),
    ]);
};
