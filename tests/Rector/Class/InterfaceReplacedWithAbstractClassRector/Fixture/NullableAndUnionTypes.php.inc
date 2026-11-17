<?php

namespace Shopware\Core\Checkout\Cart;

interface CartPersisterInterface {}
abstract class AbstractCartPersister implements CartPersisterInterface {}
class SomeBaseClass {}

class MyClass
{
    public function __construct(
        private ?CartPersisterInterface $nullable,
        private CartPersisterInterface|AbstractCartPersister $union,
        private CartPersisterInterface|SomeBaseClass|null $unionWithNull,
    ) {
    }
}
-----
<?php

namespace Shopware\Core\Checkout\Cart;

interface CartPersisterInterface {}
abstract class AbstractCartPersister implements CartPersisterInterface {}
class SomeBaseClass {}

class MyClass
{
    public function __construct(
        private ?\Shopware\Core\Checkout\Cart\AbstractCartPersister $nullable,
        private AbstractCartPersister $union,
        private \Shopware\Core\Checkout\Cart\AbstractCartPersister|SomeBaseClass|null $unionWithNull,
    ) {
    }
}
