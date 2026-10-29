<?php

class Limiter extends \Shopware\Core\Framework\RateLimiter\RateLimiter
{
    public function ensureAccepted(string $route, string $key): void
    {
        parent::ensureAccepted($route, $key);
    }
}
class Thumbnails extends \Shopware\Core\Content\Media\Thumbnail\ThumbnailService
{
    public function updateThumbnails($media, $context, bool $strict): int
    {
        return parent::updateThumbnails($media, $context, $strict);
    }
}
-----
<?php

class Limiter extends \Shopware\Core\Framework\RateLimiter\RateLimiter
{
    public function ensureAccepted(string $route, string $key, ?string $salesChannelId = null): void
    {
        parent::ensureAccepted($route, $key, salesChannelId: $salesChannelId);
    }
}
class Thumbnails extends \Shopware\Core\Content\Media\Thumbnail\ThumbnailService
{
    public function updateThumbnails($media, $context, bool $strict, bool $force = false): int
    {
        return parent::updateThumbnails($media, $context, $strict, force: $force);
    }
}
