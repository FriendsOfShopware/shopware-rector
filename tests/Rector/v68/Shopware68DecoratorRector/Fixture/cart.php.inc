<?php

class PersisterDecorator extends \Shopware\Core\Checkout\Cart\AbstractCartPersister
{
    public function __construct(private \Shopware\Core\Checkout\Cart\AbstractCartPersister $inner) {}
    public function getDecorated(): \Shopware\Core\Checkout\Cart\AbstractCartPersister { return $this->inner; }
}
-----
<?php

class PersisterDecorator extends \Shopware\Core\Checkout\Cart\AbstractCartPersister
{
    public function __construct(private \Shopware\Core\Checkout\Cart\AbstractCartPersister $inner) {}
    public function getDecorated(): \Shopware\Core\Checkout\Cart\AbstractCartPersister { return $this->inner; }
    public function exists(string $token, \Shopware\Core\System\SalesChannel\SalesChannelContext $context): bool
    {
        return $this->getDecorated()->exists($token, $context);
    }
}
