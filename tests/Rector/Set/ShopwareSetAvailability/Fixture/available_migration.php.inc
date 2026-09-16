<?php

use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\AbstractCartPersister;
use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;

function isRecalculation(CartBehavior $behavior): bool
{
    return $behavior->isRecalculation();
}

function permission(): string
{
    return AbstractCartPersister::PERSIST_CART_ERROR_PERMISSION;
}

class CustomBuilder implements ProductStreamBuilderInterface {}

?>
-----
<?php

use Shopware\Core\Checkout\CheckoutPermissions;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\AbstractCartPersister;
use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;

function isRecalculation(CartBehavior $behavior): bool
{
    return $behavior->hasPermission(CheckoutPermissions::SKIP_CART_PERSISTENCE);
}

function permission(): string
{
    return CheckoutPermissions::PERSIST_CART_ERROR;
}

class CustomBuilder extends \Shopware\Core\Content\ProductStream\Service\AbstractProductStreamBuilder
{
}

?>
