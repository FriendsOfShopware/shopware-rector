<?php

namespace Shopware\Core\Checkout\Cart;

interface CartPersisterInterface {}
abstract class AbstractCartPersister implements CartPersisterInterface {}
class SomeBaseClass {}

class CartPersister extends AbstractCartPersister implements CartPersisterInterface
{
}

class MyClass
{
    public function __construct(private CartPersister $cartPersister)
    {
    }
}
-----
<?php

namespace Shopware\Core\Checkout\Cart;

interface CartPersisterInterface {}
abstract class AbstractCartPersister implements CartPersisterInterface {}
class SomeBaseClass {}

class CartPersister extends AbstractCartPersister
{
}

class MyClass
{
    public function __construct(private CartPersister $cartPersister)
    {
    }
}
