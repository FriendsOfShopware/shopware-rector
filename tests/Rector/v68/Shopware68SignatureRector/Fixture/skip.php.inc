<?php

class OtherLimiter { public function ensureAccepted(string $route, string $key): void {} }
class ModernLimiter extends \Shopware\Core\Framework\RateLimiter\RateLimiter
{
    public function ensureAccepted(string $route, string $key, ?string $salesChannelId = null): void {}
}
class Collision extends \Shopware\Core\Framework\RateLimiter\RateLimiter
{
    public function ensureAccepted(string $route, string $key): void { $salesChannelId = 'custom'; }
}
