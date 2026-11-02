<?php

declare(strict_types=1);

use Frosh\Rector\Rule\v68\FilterablePriceDefinitionRector;
use Frosh\Rector\Rule\v68\ReflectionHelperToNativeReflectionRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    // These replacements are already supported by Shopware 6.7.0.0.
    $rectorConfig->rules([
        FilterablePriceDefinitionRector::class,
        ReflectionHelperToNativeReflectionRector::class,
    ]);
};
