<?php declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\CookieProviderToCookieGroupCollectListenerRector\Fixture\SkipConditionalGroups;

use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;

class ConditionalCookieProvider implements CookieProviderInterface
{
    public function __construct(private readonly CookieProviderInterface $inner, private readonly bool $active)
    {
    }

    public function getCookieGroups(): array
    {
        $groups = $this->inner->getCookieGroups();
        if ($this->active) {
            $groups[] = ['snippet_name' => 'cookie.active', 'cookie' => 'active'];
        }

        return $groups;
    }
}
