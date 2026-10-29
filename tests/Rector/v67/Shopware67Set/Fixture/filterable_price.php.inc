<?php

namespace EarlyPrice;

use Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface;
use Shopware\Core\Framework\Rule\Rule;

class CustomPrice implements PriceDefinitionInterface
{
    public function getFilter(): ?Rule { return null; }
    public function getType(): string { return 'custom'; }
    public function getPriority(): int { return 1; }
    public static function getConstraints(): array { return []; }
}
-----
<?php

namespace EarlyPrice;

use Shopware\Core\Checkout\Cart\Price\Struct\FilterableInterface;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface;
use Shopware\Core\Framework\Rule\Rule;

class CustomPrice implements PriceDefinitionInterface, FilterableInterface
{
    public function getFilter(): ?Rule { return null; }
    public function getType(): string { return 'custom'; }
    public function getPriority(): int { return 1; }
    public static function getConstraints(): array { return []; }
}
