<?php

namespace Shopware\Core\Checkout\Cart;

interface CartPersisterInterface {}
abstract class AbstractCartPersister implements CartPersisterInterface {}
class SomeBaseClass {}

class MyPersister extends SomeBaseClass implements CartPersisterInterface
{
}
