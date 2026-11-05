# Shopware 6.8 migrations

The 6.8 set imports the [6.7 forward-compatible rules](shopware-6.7.md) and adds migrations that need APIs introduced after Shopware 6.7.0.0.

```php
use Frosh\Rector\Set\ShopwareSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withSets([ShopwareSetList::SHOPWARE_6_8_0]);
```

An extension that still supports an older 6.7 patch can enable a single rule instead, once its minimum version provides the API the rule needs.

## Included migrations

### Cookie providers

`CookieProviderToCookieGroupCollectListenerRector` migrates decorators of the deprecated `CookieProviderInterface` to listeners of the `CookieGroupCollectEvent`.
The [event exists since 6.7.3.0](https://github.com/shopware/shopware/blob/v6.7.3.0/src/Core/Content/Cookie/Event/CookieGroupCollectEvent.php), and the interface is removed in 6.9.

```diff
-class MapsCookieProvider implements CookieProviderInterface
+class MapsCookieProvider
 {
-    public function __construct(private readonly CookieProviderInterface $inner)
-    {
-    }
-
-    public function getCookieGroups(): array
+    public function __invoke(CookieGroupCollectEvent $event): void
     {
-        return array_merge($this->inner->getCookieGroups(), [[
-            'snippet_name' => 'cookie.maps',
-            'cookie' => 'maps-enabled',
-            'value' => '1',
-            'expiration' => '30',
-        ]]);
+        if (!$event->cookieGroupCollection->has('cookie.maps')) {
+            $cookieGroup = new CookieGroup('cookie.maps');
+            $cookieGroup->setCookie('maps-enabled');
+            $cookieGroup->value = '1';
+            $cookieGroup->expiration = 30;
+            $event->cookieGroupCollection->add($cookieGroup);
+        }
     }
 }
```

The rule only migrates the common static shape: `getCookieGroups()` returns `array_merge()` of the inner provider and a literal list of cookie groups, directly or through a constant of the class.
Values are converted like the legacy bridge of Shopware converts them, for example `'30'` becomes the integer expiration `30`.
Cookie entries are added to an existing group of the same name, such as `cookie.groupStatistical`, or to a new group.
A standalone cookie group is only added when no group of the same name exists.
The rule skips classes with conditional logic, dynamic values, unknown array keys, or other usages of the inner provider.

Rector can only change PHP code.
The service definition still decorates `CookieProviderInterface` and passes the inner service.
Register the class as event listener instead:

```xml
<service id="MyPlugin\Framework\Cookie\MapsCookieProvider">
    <tag name="kernel.event_listener" event="Shopware\Core\Content\Cookie\Event\CookieGroupCollectEvent"/>
</service>
```

`shopware-cli extension fix` runs this rule and rewires the services of migrated classes in services.xml or services.yaml.
It picks the Rector set by the minimum Shopware version of the extension.
An extension that still supports 6.7 needs a `rector.php` in its root that enables the rule:

```php
use Frosh\Rector\Rule\v68\CookieProviderToCookieGroupCollectListenerRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([CookieProviderToCookieGroupCollectListenerRector::class]);
```
