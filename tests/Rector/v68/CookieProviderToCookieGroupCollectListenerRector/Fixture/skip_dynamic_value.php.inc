<?php declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\CookieProviderToCookieGroupCollectListenerRector\Fixture\SkipDynamicValue;

use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;

class DynamicCookieProvider implements CookieProviderInterface
{
    public function __construct(private readonly CookieProviderInterface $inner, private readonly string $cookieName)
    {
    }

    public function getCookieGroups(): array
    {
        return array_merge($this->inner->getCookieGroups(), [
            ['snippet_name' => 'cookie.dynamic', 'cookie' => $this->cookieName],
        ]);
    }
}
