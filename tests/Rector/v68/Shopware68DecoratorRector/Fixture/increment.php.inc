<?php

class IncrementDecorator extends \Shopware\Core\System\NumberRange\ValueGenerator\Pattern\IncrementStorage\AbstractIncrementStorage
{
    public function __construct(private \Shopware\Core\System\NumberRange\ValueGenerator\Pattern\IncrementStorage\AbstractIncrementStorage $inner) {}
    public function getDecorated(): \Shopware\Core\System\NumberRange\ValueGenerator\Pattern\IncrementStorage\AbstractIncrementStorage { return $this->inner; }
}
-----
<?php

class IncrementDecorator extends \Shopware\Core\System\NumberRange\ValueGenerator\Pattern\IncrementStorage\AbstractIncrementStorage
{
    public function __construct(private \Shopware\Core\System\NumberRange\ValueGenerator\Pattern\IncrementStorage\AbstractIncrementStorage $inner) {}
    public function getDecorated(): \Shopware\Core\System\NumberRange\ValueGenerator\Pattern\IncrementStorage\AbstractIncrementStorage { return $this->inner; }
    public function increaseToAtLeast(string $configurationId, int $value): void
    {
        $this->getDecorated()->increaseToAtLeast($configurationId, $value);
    }
}
