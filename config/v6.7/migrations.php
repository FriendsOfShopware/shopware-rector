<?php

declare(strict_types=1);

use Frosh\Rector\Rule\v68\FilterablePriceDefinitionRector;
use Frosh\Rector\Rule\v68\ReflectionHelperToNativeReflectionRector;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;

return static function (RectorConfig $rectorConfig): void {
    // These replacements are already supported by Shopware 6.7.0.0.
    $rectorConfig->rules([
        FilterablePriceDefinitionRector::class,
        ReflectionHelperToNativeReflectionRector::class,
    ]);

    $rectorConfig->ruleWithConfiguration(
        RenameClassConstFetchRector::class,
        [
            new RenameClassAndConstFetch('Shopware\Storefront\Framework\AffiliateTracking\AffiliateTrackingListener', 'AFFILIATE_CODE_KEY', 'Shopware\Core\Checkout\Order\SalesChannel\OrderService', 'AFFILIATE_CODE_KEY'),
            new RenameClassAndConstFetch('Shopware\Storefront\Framework\AffiliateTracking\AffiliateTrackingListener', 'CAMPAIGN_CODE_KEY', 'Shopware\Core\Checkout\Order\SalesChannel\OrderService', 'CAMPAIGN_CODE_KEY'),
        ],
    );
};
