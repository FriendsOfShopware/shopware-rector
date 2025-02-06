<?php

declare(strict_types=1);

use Frosh\Rector\Rule\ClassMethod\ChangeReturnTypeOfClassMethod;
use Frosh\Rector\Rule\ClassMethod\ChangeReturnTypeOfClassMethodRector;
use Frosh\Rector\Rule\ClassMethod\ElasticsearchChangeDefinitionReturnType;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../../../../../config/config_test.php');
    $rectorConfig->ruleWithConfiguration(ChangeReturnTypeOfClassMethodRector::class, [
        new ChangeReturnTypeOfClassMethod('\Shopware\Elasticsearch\Framework\AbstractElasticsearchDefinition', 'buildTermQuery', 'OpenSearchDSL\BuilderInterface'),
    ]);
};
