<?php

namespace Frosh\Rector\Tests\Rector\v68\CookieProviderToCookieGroupCollectListenerRector\Fixture\Entries;

use Shopware\Storefront\Framework\Cookie\CookieProviderInterface as LegacyProvider;

final class CustomCookieProvider implements LegacyProvider
{
    private const entries = [
        [
            'snippet_name' => 'cookie.analytics',
            'cookie' => '_my_analytics',
            'expiration' => '30',
            'value' => 1,
            'hidden' => false,
        ],
    ];

    public function __construct(private LegacyProvider $inner)
    {
    }

    public function getCookieGroups(): array
    {
        return array_merge($this->inner->getCookieGroups(), [
            [
                'snippet_name' => 'cookie.groupStatistical',
                'entries' => self::entries,
            ],
            [
                'snippet_name' => 'cookie.groupChat',
                'snippet_description' => 'cookie.groupChatDescription',
                'entries' => [
                    [
                        'snippet_name' => 'cookie.chat',
                        'snippet_description' => '',
                        'cookie' => 'chat-enabled',
                        'hidden' => true,
                    ],
                ],
            ],
        ]);
    }
}
-----
<?php

namespace Frosh\Rector\Tests\Rector\v68\CookieProviderToCookieGroupCollectListenerRector\Fixture\Entries;

use Shopware\Core\Content\Cookie\Event\CookieGroupCollectEvent;
use Shopware\Core\Content\Cookie\Struct\CookieEntry;
use Shopware\Core\Content\Cookie\Struct\CookieEntryCollection;
use Shopware\Core\Content\Cookie\Struct\CookieGroup;

final class CustomCookieProvider
{
    public function __invoke(CookieGroupCollectEvent $event): void
    {
        $cookieGroup = $event->cookieGroupCollection->get('cookie.groupStatistical');
        if ($cookieGroup === null) {
            $cookieGroup = new CookieGroup('cookie.groupStatistical');
            $event->cookieGroupCollection->add($cookieGroup);
        }
        if ($cookieGroup->getEntries() === null && $cookieGroup->getCookie() === null) {
            $cookieGroup->setEntries(new CookieEntryCollection());
        }
        $cookieEntry = new CookieEntry('_my_analytics');
        $cookieEntry->name = 'cookie.analytics';
        $cookieEntry->value = '1';
        $cookieEntry->expiration = 30;
        $cookieEntry->hidden = false;
        $cookieGroup->getEntries()?->add($cookieEntry);
        $cookieGroup = $event->cookieGroupCollection->get('cookie.groupChat');
        if ($cookieGroup === null) {
            $cookieGroup = new CookieGroup('cookie.groupChat');
            $cookieGroup->description = 'cookie.groupChatDescription';
            $event->cookieGroupCollection->add($cookieGroup);
        }
        if ($cookieGroup->getEntries() === null && $cookieGroup->getCookie() === null) {
            $cookieGroup->setEntries(new CookieEntryCollection());
        }
        $cookieEntry = new CookieEntry('chat-enabled');
        $cookieEntry->name = 'cookie.chat';
        $cookieEntry->hidden = true;
        $cookieGroup->getEntries()?->add($cookieEntry);
    }
}
