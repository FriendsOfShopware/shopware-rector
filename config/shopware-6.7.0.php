<?php

declare(strict_types=1);

use Frosh\Rector\Rule\ClassMethod\ChangeReturnTypeOfClassMethod;
use Frosh\Rector\Rule\ClassMethod\ChangeReturnTypeOfClassMethodRector;
use Frosh\Rector\Rule\v67\AddEntityNameToEntityExtension;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/v6.7/renaming.php');

    $rectorConfig->ruleWithConfiguration(AddEntityNameToEntityExtension::class, [
        'backwardsCompatible' => false,
    ]);

    $rectorConfig->ruleWithConfiguration(ChangeReturnTypeOfClassMethodRector::class, [
        new ChangeReturnTypeOfClassMethod('\Shopware\Elasticsearch\Framework\AbstractElasticsearchDefinition', 'buildTermQuery', 'OpenSearchDSL\BuilderInterface'),
    ]);

    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);
};
