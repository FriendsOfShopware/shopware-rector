<?php

class CustomRules extends \Shopware\Core\Framework\Rule\Container\Container
{
    public function __wakeup(): void
    {
        $this->initialize();
    }
}
-----
<?php

class CustomRules extends \Shopware\Core\Framework\Rule\Container\Container
{
    public function __unserialize(array $data): void
    {
        parent::__unserialize($data);
        $this->initialize();
    }
}
