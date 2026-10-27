<?php

class CustomPrice implements \Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface
{
    public function getFilter(): ?\Shopware\Core\Framework\Rule\Rule { return null; }
}
-----
<?php

class CustomPrice implements \Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface, \Shopware\Core\Checkout\Cart\Price\Struct\FilterableInterface
{
    public function getFilter(): ?\Shopware\Core\Framework\Rule\Rule { return null; }
}
