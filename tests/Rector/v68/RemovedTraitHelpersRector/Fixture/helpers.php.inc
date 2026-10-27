<?php

class HelperTest
{
    use \Shopware\Core\Framework\Test\Api\Serializer\AssertValuesTrait;
    use \Shopware\Core\Checkout\Promotion\Cart\PromotionCartInformationTrait;

    public function run($expected, $actual, $cart, $code, $name): void
    {
        $this->assertValues($expected, $actual);
        $this->addPromotionNotFoundError($code, $cart);
        $this->addPromotionNotEligibleError(name: $name, cart: $cart);
    }
}
-----
<?php

class HelperTest
{
    use \Shopware\Core\Checkout\Promotion\Cart\PromotionCartInformationTrait;

    public function run($expected, $actual, $cart, $code, $name): void
    {
        \Shopware\Core\Test\Assert\ArrayValues::assertValues($expected, $actual);
        $cart->addErrors(new \Shopware\Core\Checkout\Promotion\Cart\Error\PromotionNotFoundError($code));
        $cart->addErrors(new \Shopware\Core\Checkout\Promotion\Cart\Error\PromotionNotEligibleError($name));
    }
}
