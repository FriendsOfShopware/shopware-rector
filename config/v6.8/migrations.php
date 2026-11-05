<?php

declare(strict_types=1);

use Frosh\Rector\Rule\v68\CookieProviderToCookieGroupCollectListenerRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    // These migrations need APIs that Shopware 6.7.0.0 does not provide yet, so they are not part of the 6.7 set.
    $rectorConfig->rules([
        // CookieGroupCollectEvent is available since Shopware 6.7.3.0.
        CookieProviderToCookieGroupCollectListenerRector::class,
    ]);
};
