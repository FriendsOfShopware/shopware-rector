<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\StaticCall\RenameStaticMethodRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Rector\Renaming\ValueObject\RenameStaticMethod;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(
        RenameMethodRector::class,
        [
            new MethodCallRename('Shopware\Elasticsearch\Framework\Indexing\IndexerOffset', 'setNextDefinition', 'selectNextDefinition'),
            new MethodCallRename('Shopware\Elasticsearch\Framework\Indexing\IndexerOffset', 'setNextLanguage', 'selectNextLanguage'),
        ],
    );

    $rectorConfig->ruleWithConfiguration(
        RenameStaticMethodRector::class,
        [
            new RenameStaticMethod('Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\JsonFieldSerializer', 'encodeJson', 'Shopware\Core\Framework\Util\Json', 'encode'),
        ],
    );

    $rectorConfig->ruleWithConfiguration(
        RenameClassRector::class,
        [
            'Shopware\\Core\\Framework\\DataAbstractionLayer\\Event\\BeforeDeleteEvent' => 'Shopware\\Core\\Framework\\DataAbstractionLayer\\Event\\EntityDeleteEvent',
        ]
    );

    $rectorConfig->ruleWithConfiguration(
        RenameClassConstFetchRector::class,
        [
            new RenameClassAndConstFetch('Shopware\\Elasticsearch\\Product\\ElasticsearchProductDefinition', 'KEYWORD_FIELD', 'Shopware\\Elasticsearch\\Framework\\AbstractElasticsearchDefinition', 'KEYWORD_FIELD'),
            new RenameClassAndConstFetch('Shopware\\Elasticsearch\\Product\\ElasticsearchProductDefinition', 'BOOLEAN_FIELD', 'Shopware\\Elasticsearch\\Framework\\AbstractElasticsearchDefinition', 'BOOLEAN_FIELD'),
            new RenameClassAndConstFetch('Shopware\\Elasticsearch\\Product\\ElasticsearchProductDefinition', 'FLOAT_FIELD', 'Shopware\\Elasticsearch\\Framework\\AbstractElasticsearchDefinition', 'FLOAT_FIELD'),
            new RenameClassAndConstFetch('Shopware\\Elasticsearch\\Product\\ElasticsearchProductDefinition', 'INT_FIELD', 'Shopware\\Elasticsearch\\Framework\\AbstractElasticsearchDefinition', 'INT_FIELD'),
            new RenameClassAndConstFetch('Shopware\\Elasticsearch\\Product\\ElasticsearchProductDefinition', 'SEARCH_FIELD', 'Shopware\\Elasticsearch\\Framework\\AbstractElasticsearchDefinition', 'SEARCH_FIELD'),
        ]
    );
};
