<?php

namespace Shopware\Tests\Rector\Fixture;

use Shopware\Core\Checkout\Cart\CartBehavior;

function checkRecalculation(CartBehavior $behavior): bool
{
    return $behavior->isRecalculation();
}

function checkNullableRecalculation(?CartBehavior $behavior): ?bool
{
    return $behavior?->isRecalculation();
}

?>
-----
<?php

namespace Shopware\Tests\Rector\Fixture;

use Shopware\Core\Checkout\Cart\CartBehavior;

function checkRecalculation(CartBehavior $behavior): bool
{
    return $behavior->hasPermission(\Shopware\Core\Checkout\CheckoutPermissions::SKIP_CART_PERSISTENCE);
}

function checkNullableRecalculation(?CartBehavior $behavior): ?bool
{
    return $behavior?->hasPermission(\Shopware\Core\Checkout\CheckoutPermissions::SKIP_CART_PERSISTENCE);
}

?>
