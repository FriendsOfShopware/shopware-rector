<?php

declare(strict_types=1);

use Frosh\Rector\Rule\Class_\InterfaceReplacedWithAbstractClass;
use Frosh\Rector\Rule\Class_\InterfaceReplacedWithAbstractClassRector;
use Frosh\Rector\Rule\v68\ProductStreamBuilderBuildFiltersToEnrichCriteriaRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(ProductStreamBuilderBuildFiltersToEnrichCriteriaRector::class);
    $rectorConfig->ruleWithConfiguration(
        InterfaceReplacedWithAbstractClassRector::class,
        [
            new InterfaceReplacedWithAbstractClass(
                'Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface',
                '\Shopware\Core\Content\ProductStream\Service\AbstractProductStreamBuilder',
            ),
        ],
    );
};
