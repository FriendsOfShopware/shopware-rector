<?php declare(strict_types=1);

namespace Shopware\Storefront\Framework\Cookie;

interface CookieProviderInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getCookieGroups(): array;
}
