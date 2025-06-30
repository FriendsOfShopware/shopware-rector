<?php

namespace Shopware\Core\Checkout\Cart;

interface CartPersisterInterface {}
abstract class AbstractCartPersister {}

class MyClass implements CartPersisterInterface
{
    private CartPersisterInterface $cartPersister;

    public function __construct(CartPersisterInterface $cartPersister)
    {
        $this->cartPersister = $cartPersister;
    }
}
-----
<?php

namespace Shopware\Core\Checkout\Cart;

interface CartPersisterInterface {}
abstract class AbstractCartPersister {}

class MyClass extends \Shopware\Core\Checkout\Cart\AbstractCartPersister
{
    private \Shopware\Core\Checkout\Cart\AbstractCartPersister $cartPersister;
    public function __construct(\Shopware\Core\Checkout\Cart\AbstractCartPersister $cartPersister)
    {
        $this->cartPersister = $cartPersister;
    }
}
