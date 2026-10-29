<?php

class HasUnserialize extends \Shopware\Core\Framework\Rule\Container\Container
{
    public function __wakeup(): void {}
    public function __unserialize(array $data): void {}
}
class HasData extends \Shopware\Core\Framework\Rule\Container\Container
{
    public function __wakeup(): void { $data = loadData(); }
}
class Unrelated
{
    public function __wakeup(): void {}
}
