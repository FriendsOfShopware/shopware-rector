<?php

#[\Symfony\Component\Routing\Attribute\Route(defaults: [\Shopware\Core\PlatformRequest::ATTRIBUTE_HTTP_CACHE => ['states' => ['cart-filled'], 'maxAge' => 60]])]
class Controller {}
$other = ['states' => ['cart-filled']];
-----
<?php

#[\Symfony\Component\Routing\Attribute\Route(defaults: [\Shopware\Core\PlatformRequest::ATTRIBUTE_HTTP_CACHE => ['maxAge' => 60]])]
class Controller {}
$other = ['states' => ['cart-filled']];
