<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Cart\Price\Struct;

interface FilterableInterface
{
    public function getFilter(): ?\Shopware\Core\Framework\Rule\Rule;
}
