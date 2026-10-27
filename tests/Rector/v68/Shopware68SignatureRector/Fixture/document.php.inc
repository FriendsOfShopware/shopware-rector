<?php

class Document extends \Shopware\Core\Checkout\Document\DocumentEntity
{
    public function setOrderId(string $orderId): void { parent::setOrderId($orderId); }
    public function setOrderVersionId(string $version): void { parent::setOrderVersionId($version); }
}
-----
<?php

class Document extends \Shopware\Core\Checkout\Document\DocumentEntity
{
    public function setOrderId(?string $orderId): void { parent::setOrderId($orderId); }
    public function setOrderVersionId(?string $version): void { parent::setOrderVersionId($version); }
}
