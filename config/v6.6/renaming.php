<?php

declare(strict_types=1);

use Frosh\Rector\Rule\Class_\InterfaceReplacedWithAbstractClass;
use Frosh\Rector\Rule\Class_\InterfaceReplacedWithAbstractClassRector;
use Frosh\Rector\Rule\ClassConstructor\RemoveArgumentFromClassConstruct;
use Frosh\Rector\Rule\ClassConstructor\RemoveArgumentFromClassConstructRector;
use Frosh\Rector\Rule\v65\FakerPropertyToMethodCallRector;
use Frosh\Rector\Rule\v65\MigrateCaptchaAnnotationToRouteRector;
use Frosh\Rector\Rule\v65\MigrateLoginRequiredAnnotationToRouteRector;
use Frosh\Rector\Rule\v65\MigrateRouteScopeToRouteDefaults;
use Frosh\Rector\Rule\v65\RedisConnectionFactoryCreateRector;
use Frosh\Rector\Rule\v65\ThemeCompilerPrefixRector;
use Frosh\Rector\Rule\v65\ThumbnailGenerateSingleToMultiGenerateRector;
use Rector\Arguments\Rector\MethodCall\RemoveMethodCallParamRector;
use Rector\Arguments\ValueObject\RemoveMethodCallParam;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Transform\Rector\Assign\PropertyFetchToMethodCallRector;
use Rector\Transform\ValueObject\PropertyFetchToMethodCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(
        RenameMethodRector::class,
        [
            new MethodCallRename('Shopware\Elasticsearch\Framework\Indexing\IndexerOffset', 'setNextDefinition', 'selectNextDefinition'),
            new MethodCallRename('Shopware\Elasticsearch\Framework\Indexing\IndexerOffset', 'setNextLanguage', 'selectNextLanguage'),
        ],
    );
};
