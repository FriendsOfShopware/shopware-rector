<?php declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\CookieProviderToCookieGroupCollectListenerRector\Fixture\SkipUnknownKey;

use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;

class UnknownKeyCookieProvider implements CookieProviderInterface
{
    public function __construct(private readonly CookieProviderInterface $inner)
    {
    }

    public function getCookieGroups(): array
    {
        return array_merge($this->inner->getCookieGroups(), [
            ['snippet_name' => 'cookie.default', 'cookie' => 'default', 'default' => true],
        ]);
    }
}
