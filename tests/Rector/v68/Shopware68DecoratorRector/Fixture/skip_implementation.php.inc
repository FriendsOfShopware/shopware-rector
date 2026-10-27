<?php

class Storage extends \Shopware\Core\Checkout\Cart\AbstractCartPersister
{
    public function getDecorated(): \Shopware\Core\Checkout\Cart\AbstractCartPersister { throw new \LogicException(); }
}
