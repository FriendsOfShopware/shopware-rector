<?php

use Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Test\TestCaseHelper\ReflectionHelper;

class EarlyPrice implements PriceDefinitionInterface
{
    public function getFilter(): ?Rule { return null; }
}

ReflectionHelper::getProperty(EarlyPrice::class, 'filter');
-----
<?php

use Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\Framework\Test\TestCaseHelper\ReflectionHelper;

class EarlyPrice implements PriceDefinitionInterface, \Shopware\Core\Checkout\Cart\Price\Struct\FilterableInterface
{
    public function getFilter(): ?Rule { return null; }
}

new \ReflectionProperty(objectOrClass: EarlyPrice::class, property: 'filter');
