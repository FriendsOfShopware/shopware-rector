<?php

function token(?\Shopware\Core\Framework\Api\OAuth\AccessToken $token): \Shopware\Core\Framework\Api\OAuth\AccessToken|false { return $token ?? false; }
class Service
{
    public function __construct(private \Shopware\Core\Framework\Api\OAuth\Client\ApiClient $client) {}
    public function getBuilder(): \Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface {}
}
new \Shopware\Core\Framework\Api\OAuth\AccessToken();
function duplicate(\Shopware\Core\Framework\Api\OAuth\AccessToken|\League\OAuth2\Server\Entities\AccessTokenEntityInterface $token): void {}
-----
<?php

function token(?\League\OAuth2\Server\Entities\AccessTokenEntityInterface $token): \League\OAuth2\Server\Entities\AccessTokenEntityInterface|false { return $token ?? false; }
class Service
{
    public function __construct(private \League\OAuth2\Server\Entities\ClientEntityInterface $client) {}
    public function getBuilder(): \Shopware\Core\Content\ProductStream\Service\AbstractProductStreamBuilder {}
}
new \Shopware\Core\Framework\Api\OAuth\AccessToken();
function duplicate(\League\OAuth2\Server\Entities\AccessTokenEntityInterface $token): void {}
