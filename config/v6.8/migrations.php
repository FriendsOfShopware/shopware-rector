<?php

declare(strict_types=1);

use Frosh\Rector\Rule\v68\ConstraintOptionsToNamedArgumentsRector;
use Frosh\Rector\Rule\v68\EntityDefinitionParentConstructorRector;
use Frosh\Rector\Rule\v68\FilterablePriceDefinitionRector;
use Frosh\Rector\Rule\v68\MailFlowCriteriaEventRector;
use Frosh\Rector\Rule\v68\ReflectionHelperToNativeReflectionRector;
use Frosh\Rector\Rule\v68\RemovedTraitHelpersRector;
use Frosh\Rector\Rule\v68\RuleContainerUnserializeRector;
use Frosh\Rector\Rule\v68\Shopware68ConstructorRector;
use Frosh\Rector\Rule\v68\Shopware68ExceptionRector;
use Frosh\Rector\Rule\v68\Shopware68SignatureRector;
use Frosh\Rector\Rule\v68\Shopware68TypeRector;
use Frosh\Rector\Rule\v68\Shopware68MethodCallRector;
use Frosh\Rector\Rule\v68\PrimaryOrderAssociationRector;
use Frosh\Rector\Rule\v68\CacheRouteStatesRector;
use Frosh\Rector\Rule\v68\CustomerEmailConstraintContextRector;
use Frosh\Rector\Rule\v68\Shopware68PropertyReadRector;
use Frosh\Rector\Rule\v68\RemoteThumbnailMediaRector;
use Frosh\Rector\Rule\v68\TranslationLoaderLocaleRector;
use Frosh\Rector\Rule\v68\Shopware68DecoratorRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        Shopware68MethodCallRector::class,
        Shopware68DecoratorRector::class,
        PrimaryOrderAssociationRector::class,
        CacheRouteStatesRector::class,
        CustomerEmailConstraintContextRector::class,
        Shopware68PropertyReadRector::class,
        RemoteThumbnailMediaRector::class,
        TranslationLoaderLocaleRector::class,

        ConstraintOptionsToNamedArgumentsRector::class,
        EntityDefinitionParentConstructorRector::class,
        FilterablePriceDefinitionRector::class,
        MailFlowCriteriaEventRector::class,
        ReflectionHelperToNativeReflectionRector::class,
        RemovedTraitHelpersRector::class,
        RuleContainerUnserializeRector::class,
        Shopware68ConstructorRector::class,
        Shopware68ExceptionRector::class,
        Shopware68SignatureRector::class,
        Shopware68TypeRector::class,
    ]);
};
