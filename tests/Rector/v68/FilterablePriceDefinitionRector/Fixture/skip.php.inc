<?php

class OtherPrice
{
    public function getFilter(): ?\Shopware\Core\Framework\Rule\Rule { return null; }
}
class ExistingPrice implements \Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface, \Shopware\Core\Checkout\Cart\Price\Struct\FilterableInterface
{
    public function getFilter(): ?\Shopware\Core\Framework\Rule\Rule { return null; }
}
