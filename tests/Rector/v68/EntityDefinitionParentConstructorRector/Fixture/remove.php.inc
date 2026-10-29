<?php

class Definition extends \Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition
{
    public function __construct(private array $metadata = [])
    {
        parent::__construct();
        $this->metadata['loaded'] = true;
    }
}
-----
<?php

class Definition extends \Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition
{
    public function __construct(private array $metadata = [])
    {
        $this->metadata['loaded'] = true;
    }
}
