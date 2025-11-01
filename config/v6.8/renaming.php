<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(
        RenameClassConstFetchRector::class,
        [
            new RenameClassAndConstFetch('Shopware\Core\Checkout\Cart\AbstractCartPersister', 'PERSIST_CART_ERROR_PERMISSION', 'Shopware\Core\Checkout\CheckoutPermissions', 'PERSIST_CART_ERROR'),

            new RenameClassAndConstFetch('Shopware\Core\Checkout\Cart\Delivery\DeliveryProcessor', 'SKIP_DELIVERY_PRICE_RECALCULATION', 'Shopware\Core\Checkout\CheckoutPermissions', 'SKIP_PRODUCT_STOCK_VALIDATION'),
            new RenameClassAndConstFetch('Shopware\Core\Checkout\Cart\Delivery\DeliveryProcessor', 'SKIP_DELIVERY_TAX_RECALCULATION', 'Shopware\Core\Checkout\CheckoutPermissions', 'SKIP_DELIVERY_TAX_RECALCULATION'),

            new RenameClassAndConstFetch('Shopware\Core\Checkout\Promotion\Cart\PromotionCollector', 'SKIP_PROMOTION', 'Shopware\Core\Checkout\CheckoutPermissions', 'SKIP_PROMOTION'),
            new RenameClassAndConstFetch('Shopware\Core\Checkout\Promotion\Cart\PromotionCollector', 'SKIP_AUTOMATIC_PROMOTIONS', 'Shopware\Core\Checkout\CheckoutPermissions', 'SKIP_AUTOMATIC_PROMOTIONS'),
            new RenameClassAndConstFetch('Shopware\Core\Checkout\Promotion\Cart\PromotionCollector', 'PIN_MANUAL_PROMOTIONS', 'Shopware\Core\Checkout\CheckoutPermissions', 'PIN_MANUAL_PROMOTIONS'),
            new RenameClassAndConstFetch('Shopware\Core\Checkout\Promotion\Cart\PromotionCollector', 'PIN_AUTOMATIC_PROMOTIONS', 'Shopware\Core\Checkout\CheckoutPermissions', 'PIN_AUTOMATIC_PROMOTIONS'),

            new RenameClassAndConstFetch('Shopware\Core\Content\Product\Cart\ProductCartProcessor', 'ALLOW_PRODUCT_PRICE_OVERWRITES', 'Shopware\Core\Checkout\CheckoutPermissions', 'ALLOW_PRODUCT_PRICE_OVERWRITES'),
            new RenameClassAndConstFetch('Shopware\Core\Content\Product\Cart\ProductCartProcessor', 'ALLOW_PRODUCT_LABEL_OVERWRITES', 'Shopware\Core\Checkout\CheckoutPermissions', 'ALLOW_PRODUCT_LABEL_OVERWRITES'),
            new RenameClassAndConstFetch('Shopware\Core\Content\Product\Cart\ProductCartProcessor', 'SKIP_PRODUCT_RECALCULATION', 'Shopware\Core\Checkout\CheckoutPermissions', 'SKIP_PRODUCT_RECALCULATION'),
            new RenameClassAndConstFetch('Shopware\Core\Content\Product\Cart\ProductCartProcessor', 'SKIP_PRODUCT_STOCK_VALIDATION', 'Shopware\Core\Checkout\CheckoutPermissions', 'SKIP_PRODUCT_STOCK_VALIDATION'),
            new RenameClassAndConstFetch('Shopware\Core\Content\Product\Cart\ProductCartProcessor', 'KEEP_INACTIVE_PRODUCT', 'Shopware\Core\Checkout\CheckoutPermissions', 'KEEP_INACTIVE_PRODUCT'),
        ],
    );
};
