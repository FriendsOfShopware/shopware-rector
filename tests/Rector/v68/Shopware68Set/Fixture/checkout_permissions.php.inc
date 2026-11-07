<?php

use Shopware\Core\Checkout\Cart\Delivery\DeliveryProcessor;

$permissions = [
    DeliveryProcessor::SKIP_DELIVERY_PRICE_RECALCULATION,
    DeliveryProcessor::SKIP_DELIVERY_TAX_RECALCULATION,
];
-----
<?php

use Shopware\Core\Checkout\Cart\Delivery\DeliveryProcessor;

$permissions = [
    \Shopware\Core\Checkout\CheckoutPermissions::SKIP_DELIVERY_PRICE_RECALCULATION,
    \Shopware\Core\Checkout\CheckoutPermissions::SKIP_DELIVERY_TAX_RECALCULATION,
];
