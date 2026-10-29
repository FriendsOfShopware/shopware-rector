<?php declare(strict_types=1);

namespace Shopware\Storefront\Framework\Routing;

abstract class AbstractDomainLoader
{
    public function getDecorated(): self {}
}
