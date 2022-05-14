<?php declare(strict_types=1);

namespace Shopware\Core\Framework\Adapter\Cache;

class RedisConnectionFactory
{
    public function __construct(?string $prefix = null)
    {
        $this->prefix = $prefix;
    }

    public function create(string $dsn, array $options = [])
    {
    }
}
