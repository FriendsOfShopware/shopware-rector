<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Rule\Container;

abstract class Container
{
    public function __unserialize(array $data): void {}
}
