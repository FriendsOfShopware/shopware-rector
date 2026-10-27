<?php

function primary(\Shopware\Core\Checkout\Order\OrderEntity $order, \OtherOrder $other): void
{
    $order->getDeliveries()->first();
    $order?->getTransactions()?->last();
    $order->getTransactions()->first();
    $other->getDeliveries()->first();
}
-----
<?php

function primary(\Shopware\Core\Checkout\Order\OrderEntity $order, \OtherOrder $other): void
{
    $order->getPrimaryOrderDelivery();
    $order?->getPrimaryOrderTransaction();
    $order->getTransactions()->first();
    $other->getDeliveries()->first();
}
