<?php declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\CookieProviderToCookieGroupCollectListenerRector\Fixture\SkipInnerUsedElsewhere;

use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;

class InnerUsedCookieProvider implements CookieProviderInterface
{
    public function __construct(private readonly CookieProviderInterface $inner)
    {
    }

    public function getCookieGroups(): array
    {
        return array_merge($this->inner->getCookieGroups(), [
            ['snippet_name' => 'cookie.static', 'cookie' => 'static'],
        ]);
    }

    public function countGroups(): int
    {
        return count($this->inner->getCookieGroups());
    }
}
