<?php

use Shopware\Core\Checkout\Cart\CartBehavior;

function isRecalculation(CartBehavior $behavior): bool
{
    return $behavior->isRecalculation();
}

?>
-----
<?php

use Shopware\Core\Checkout\CheckoutPermissions;
use Shopware\Core\Checkout\Cart\CartBehavior;

function isRecalculation(CartBehavior $behavior): bool
{
    return $behavior->hasPermission(CheckoutPermissions::SKIP_CART_PERSISTENCE);
}

?>
