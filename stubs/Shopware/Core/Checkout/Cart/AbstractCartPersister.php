<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Cart;

abstract class AbstractCartPersister
{
    public function getDecorated(): self {}
}
