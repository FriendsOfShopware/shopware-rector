<?php declare(strict_types=1);

namespace Shopware\Core\Checkout\Cart\SalesChannel;

abstract class AbstractCartLoadRoute
{
    abstract public function getDecorated(): self;

    abstract public function load($request, $context);
}
