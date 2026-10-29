<?php

use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Content\ProductExport\Struct\ProductExportResult;
use Shopware\Core\Framework\Adapter\Cache\Http\CacheAttribute;
use Shopware\Core\Framework\Api\OAuth\Client\ApiClient;
use Shopware\Core\Content\Seo\HreflangLoaderParameter;

new CartBehavior($permissions, true, true);
new CartBehavior(isRecalculation: $dynamic);
new ProductExportResult($content, $errors, calculateTotal());
new CacheAttribute(states: getStates());
new ApiClient(...$args);
new HreflangLoaderParameter($route, [], $context);
new HreflangLoaderParameter('frontend.detail.page', [], $context);
new HreflangLoaderParameter('frontend.home.page', [], $context, true);
new ProductExportResult(content: $content, errors: $errors, offset: $offset);
