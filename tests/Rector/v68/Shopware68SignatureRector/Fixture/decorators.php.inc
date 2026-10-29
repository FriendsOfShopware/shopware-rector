<?php

class CartLoader extends \Shopware\Core\Checkout\Cart\SalesChannel\AbstractCartLoadRoute
{
    public function load($request, $context)
    {
        return $this->getDecorated()->load($request, $context);
    }
}
class Theme extends \Shopware\Storefront\Theme\ThemeService
{
    public function compileTheme(string $salesChannelId, string $themeId, $context, $configurationCollection = null, bool $withAssets = true): void
    {
        parent::compileTheme($salesChannelId, $themeId, $context, $configurationCollection, $withAssets);
    }
}
class Factory extends \Shopware\Core\Framework\RateLimiter\RateLimiterFactory
{
    public function create(?string $key = null): mixed { return parent::create($key); }
}
-----
<?php

class CartLoader extends \Shopware\Core\Checkout\Cart\SalesChannel\AbstractCartLoadRoute
{
    public function load($request, $context, ?\Shopware\Core\Checkout\Cart\Cart $cart = null)
    {
        return $this->getDecorated()->load($request, $context, cart: $cart);
    }
}
class Theme extends \Shopware\Storefront\Theme\ThemeService
{
    public function compileTheme(string $salesChannelId, string $themeId, $context, $configurationCollection = null, bool $withAssets = true, bool $notifyOnAsyncCompletion = true): void
    {
        parent::compileTheme($salesChannelId, $themeId, $context, $configurationCollection, $withAssets, notifyOnAsyncCompletion: $notifyOnAsyncCompletion);
    }
}
class Factory extends \Shopware\Core\Framework\RateLimiter\RateLimiterFactory
{
    public function create(?string $key = null, ?string $salesChannelId = null): mixed { return parent::create($key, salesChannelId: $salesChannelId); }
}
