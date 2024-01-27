<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Transform\Rector\New_\NewToStaticCallRector;
use Rector\Transform\ValueObject\NewToStaticCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(
        NewToStaticCallRector::class,
        [
            // RoutingException
            new NewToStaticCall('Shopware\Core\Framework\Routing\Exception\InvalidRequestParameterException', 'Shopware\Core\Framework\Routing\RoutingException', 'invalidRequestParameter'),
            new NewToStaticCall('Shopware\Core\Framework\Routing\Exception\MissingRequestParameterException', 'Shopware\Core\Framework\Routing\RoutingException', 'missingRequestParameter'),
            new NewToStaticCall('Shopware\Core\Framework\Routing\Exception\LanguageNotFoundException', 'Shopware\Core\Framework\Routing\RoutingException', 'languageNotFound'),

            // DataAbstractionLayerException
            new NewToStaticCall('Shopware\Core\Framework\DataAbstractionLayer\Exception\InvalidSerializerFieldException', 'Shopware\Core\Framework\DataAbstractionLayer\DataAbstractionLayerException', 'invalidSerializerField'),
            new NewToStaticCall('Shopware\Core\Framework\DataAbstractionLayer\Exception\VersionMergeAlreadyLockedException', 'Shopware\Core\Framework\DataAbstractionLayer\DataAbstractionLayerException', 'versionMergeAlreadyLocked'),

            // ElasticsearchException
            new NewToStaticCall('Shopware\Elasticsearch\Exception\UnsupportedElasticsearchDefinitionException', 'Shopware\Elasticsearch\ElasticsearchException', 'unsupportedElasticsearchDefinition'),
            new NewToStaticCall('Shopware\Elasticsearch\Exception\ElasticsearchIndexingException', 'Shopware\Elasticsearch\ElasticsearchException', 'indexingError'),
            new NewToStaticCall('Shopware\Elasticsearch\Exception\ServerNotAvailableException', 'Shopware\Elasticsearch\ElasticsearchException', 'serverNotAvailable'),

            // ProductExportException
            new NewToStaticCall('Shopware\Core\Content\ProductExport\Exception\EmptyExportException', 'Shopware\Core\Content\ProductExport\ProductExportException', 'productExportNotFound'),
            new NewToStaticCall('Shopware\Core\Content\ProductExport\Exception\RenderFooterException', 'Shopware\Core\Content\ProductExport\ProductExportException', 'renderFooterException'),
            new NewToStaticCall('Shopware\Core\Content\ProductExport\Exception\RenderHeaderException', 'Shopware\Core\Content\ProductExport\ProductExportException', 'renderHeaderException'),
            new NewToStaticCall('Shopware\Core\Content\ProductExport\Exception\RenderProductException', 'Shopware\Core\Content\ProductExport\ProductExportException', 'renderProductException'),
        ],
    );
};
