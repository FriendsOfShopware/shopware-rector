<?php

class DomainDecorator extends \Shopware\Storefront\Framework\Routing\AbstractDomainLoader
{
    public function __construct(private \Shopware\Storefront\Framework\Routing\AbstractDomainLoader $inner) {}
    public function getDecorated(): \Shopware\Storefront\Framework\Routing\AbstractDomainLoader { return $this->inner; }
    public function load(): array { return $this->inner->load(); }
}
-----
<?php

class DomainDecorator extends \Shopware\Storefront\Framework\Routing\AbstractDomainLoader
{
    public function __construct(private \Shopware\Storefront\Framework\Routing\AbstractDomainLoader $inner) {}
    public function getDecorated(): \Shopware\Storefront\Framework\Routing\AbstractDomainLoader { return $this->inner; }
    public function loadDomains(): \Shopware\Storefront\Framework\Routing\Struct\DomainCollection { return $this->inner->loadDomains(); }
}
