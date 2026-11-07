<?php declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\CookieProviderToCookieGroupCollectListenerRector\Fixture\StandaloneCookieFromConstant;

use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;

class MapsCookieProvider implements CookieProviderInterface
{
    private const mapsCookie = [
        'snippet_name' => 'myPlugin.cookie.maps',
        'snippet_description' => 'myPlugin.cookie.mapsDescription',
        'cookie' => 'my-plugin-maps',
        'value' => '1',
        'expiration' => '30',
    ];

    public function __construct(private readonly CookieProviderInterface $cookieProvider)
    {
    }

    public function getCookieGroups(): array
    {
        return array_merge(
            $this->cookieProvider->getCookieGroups(),
            [
                self::mapsCookie,
            ]
        );
    }
}
-----
<?php declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\CookieProviderToCookieGroupCollectListenerRector\Fixture\StandaloneCookieFromConstant;

use Shopware\Core\Content\Cookie\Event\CookieGroupCollectEvent;
use Shopware\Core\Content\Cookie\Struct\CookieGroup;

class MapsCookieProvider
{
    public function __invoke(CookieGroupCollectEvent $event): void
    {
        if (!$event->cookieGroupCollection->has('myPlugin.cookie.maps')) {
            $cookieGroup = new CookieGroup('myPlugin.cookie.maps');
            $cookieGroup->description = 'myPlugin.cookie.mapsDescription';
            $cookieGroup->setCookie('my-plugin-maps');
            $cookieGroup->value = '1';
            $cookieGroup->expiration = 30;
            $event->cookieGroupCollection->add($cookieGroup);
        }
    }
}
