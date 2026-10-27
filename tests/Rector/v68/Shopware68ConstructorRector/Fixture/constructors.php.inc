<?php

use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Content\ProductExport\Struct\ProductExportResult;
use Shopware\Core\Framework\Adapter\Cache\Http\CacheAttribute;
use Shopware\Core\Framework\Api\OAuth\Client\ApiClient;
use Shopware\Core\Content\Seo\HreflangLoaderParameter;

new CartBehavior($permissions, true, false);
new CartBehavior(isRecalculation: false);
new ProductExportResult($content, $errors, 0, $offset, true);
new CacheAttribute(3600, 7200, null, ['cart-filled']);
new ApiClient('id', true, 'Plugin');
new ApiClient('id', false, 'Name', false, $redirects);
new ApiClient(identifier: 'id', writeAccess: true, confidential: null);
new HreflangLoaderParameter('frontend.home.page', [], $context);
new HreflangLoaderParameter(route: 'frontend.home.page', routeParameters: [], salesChannelContext: $context, homepage: false);
new ProductExportResult($content, $errors, $total, $offset);
new CacheAttribute(states: $states);
function client(?bool $flag): void { new ApiClient('id', true, confidential: $flag); }
-----
<?php

use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Content\ProductExport\Struct\ProductExportResult;
use Shopware\Core\Framework\Adapter\Cache\Http\CacheAttribute;
use Shopware\Core\Framework\Api\OAuth\Client\ApiClient;
use Shopware\Core\Content\Seo\HreflangLoaderParameter;

new CartBehavior(permissions: $permissions, hookAware: true);
new CartBehavior();
new ProductExportResult(content: $content, errors: $errors, offset: $offset, hasNextBatch: true);
new CacheAttribute(maxAge: 3600, sMaxAge: 7200, policyModifier: null);
new ApiClient(identifier: 'id', writeAccess: true, name: 'Plugin', confidential: true);
new ApiClient(identifier: 'id', writeAccess: false, name: 'Name', confidential: false, redirectUris: $redirects);
new ApiClient(identifier: 'id', writeAccess: true, confidential: true);
new HreflangLoaderParameter(route: 'frontend.home.page', routeParameters: [], salesChannelContext: $context, homepage: true);
new HreflangLoaderParameter(route: 'frontend.home.page', routeParameters: [], salesChannelContext: $context, homepage: true);
new ProductExportResult(content: $content, errors: $errors, offset: $offset);
new CacheAttribute();
function client(?bool $flag): void { new ApiClient(identifier: 'id', writeAccess: true, confidential: $flag ?? true); }
