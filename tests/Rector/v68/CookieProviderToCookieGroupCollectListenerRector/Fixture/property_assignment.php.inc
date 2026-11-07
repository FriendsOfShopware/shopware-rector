<?php declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\CookieProviderToCookieGroupCollectListenerRector\Fixture\PropertyAssignment;

use Psr\Log\LoggerInterface;
use Shopware\Storefront\Framework\Cookie\CookieProviderInterface;

class TrackingCookieProvider implements CookieProviderInterface
{
    public const COOKIE_NAME = 'my-tracking';
    private CookieProviderInterface $originalService;
    public function __construct(CookieProviderInterface $service, private readonly LoggerInterface $logger)
    {
        $this->originalService = $service;
    }
    public function getCookieGroups(): array
    {
        return array_merge($this->originalService->getCookieGroups(), [
            [
                'snippet_name' => 'cookie.tracking.name',
                'cookie' => self::COOKIE_NAME,
                'value' => true,
                'expiration' => 365,
                'isRequired' => false,
            ],
        ]);
    }
    public function log(): void
    {
        $this->logger->info('cookie');
    }
}
-----
<?php declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68\CookieProviderToCookieGroupCollectListenerRector\Fixture\PropertyAssignment;

use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Cookie\Event\CookieGroupCollectEvent;
use Shopware\Core\Content\Cookie\Struct\CookieGroup;

class TrackingCookieProvider
{
    public const COOKIE_NAME = 'my-tracking';
    public function __construct(private readonly LoggerInterface $logger)
    {
    }
    public function __invoke(CookieGroupCollectEvent $event): void
    {
        if (!$event->cookieGroupCollection->has('cookie.tracking.name')) {
            $cookieGroup = new CookieGroup('cookie.tracking.name');
            $cookieGroup->setCookie(self::COOKIE_NAME);
            $cookieGroup->value = '1';
            $cookieGroup->expiration = 365;
            $cookieGroup->isRequired = false;
            $event->cookieGroupCollection->add($cookieGroup);
        }
    }
    public function log(): void
    {
        $this->logger->info('cookie');
    }
}
