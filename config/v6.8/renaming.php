<?php

declare(strict_types=1);

use Frosh\Rector\Rule\Class_\InterfaceReplacedWithAbstractClass;
use Frosh\Rector\Rule\Class_\InterfaceReplacedWithAbstractClassRector;
use Frosh\Rector\Rule\v68\EntitySearchResultGetEntitiesRector;
use Frosh\Rector\Rule\v68\ProductStreamBuilderBuildFiltersToEnrichCriteriaRector;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameProperty;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->rule(EntitySearchResultGetEntitiesRector::class);
    $rectorConfig->rule(ProductStreamBuilderBuildFiltersToEnrichCriteriaRector::class);

    $rectorConfig->ruleWithConfiguration(
        InterfaceReplacedWithAbstractClassRector::class,
        [
            new InterfaceReplacedWithAbstractClass(
                'Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface',
                '\Shopware\Core\Content\ProductStream\Service\AbstractProductStreamBuilder',
            ),
            new InterfaceReplacedWithAbstractClass(
                'Shopware\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface',
                '\Shopware\Core\System\NumberRange\ValueGenerator\AbstractNumberRangeValueGenerator',
            ),
        ],
    );

    $rectorConfig->ruleWithConfiguration(
        RenameClassRector::class,
        [
            'Shopware\Core\Framework\Adapter\Console\ShopwareStyle' => 'Symfony\Component\Console\Style\SymfonyStyle',
            'Shopware\Core\Framework\DataAbstractionLayer\Dbal\Exception\UnmappedFieldException' => 'Shopware\Core\Framework\DataAbstractionLayer\Exception\UnmappedFieldException',
            'Shopware\Core\Content\Flow\Events\BeforeLoadStorableFlowDataEvent' => 'Shopware\Core\Content\Shared\MailFlow\Event\MailFlowDataCriteriaEvent',
            'Shopware\Administration\Notification\NotificationCollection' => 'Shopware\Core\Framework\Notification\NotificationCollection',
            'Shopware\Administration\Notification\NotificationDefinition' => 'Shopware\Core\Framework\Notification\NotificationDefinition',
            'Shopware\Administration\Notification\NotificationEntity' => 'Shopware\Core\Framework\Notification\NotificationEntity',
            'Shopware\Elasticsearch\Product\SearchConfigLoader' => 'Shopware\Core\Framework\DataAbstractionLayer\Search\SearchConfigLoader',
        ],
    );

    $rectorConfig->ruleWithConfiguration(
        RenameClassConstFetchRector::class,
        [
            new RenameClassAndConstFetch('Shopware\Core\Checkout\Cart\AbstractCartPersister', 'PERSIST_CART_ERROR_PERMISSION', 'Shopware\Core\Checkout\CheckoutPermissions', 'PERSIST_CART_ERROR'),

            new RenameClassAndConstFetch('Shopware\Core\Checkout\Cart\Delivery\DeliveryProcessor', 'SKIP_DELIVERY_PRICE_RECALCULATION', 'Shopware\Core\Checkout\CheckoutPermissions', 'SKIP_DELIVERY_PRICE_RECALCULATION'),
            new RenameClassAndConstFetch('Shopware\Core\Checkout\Cart\Delivery\DeliveryProcessor', 'SKIP_DELIVERY_TAX_RECALCULATION', 'Shopware\Core\Checkout\CheckoutPermissions', 'SKIP_DELIVERY_TAX_RECALCULATION'),

            new RenameClassAndConstFetch('Shopware\Core\Checkout\Promotion\Cart\PromotionCollector', 'SKIP_PROMOTION', 'Shopware\Core\Checkout\CheckoutPermissions', 'SKIP_PROMOTION'),
            new RenameClassAndConstFetch('Shopware\Core\Checkout\Promotion\Cart\PromotionCollector', 'SKIP_AUTOMATIC_PROMOTIONS', 'Shopware\Core\Checkout\CheckoutPermissions', 'SKIP_AUTOMATIC_PROMOTIONS'),
            new RenameClassAndConstFetch('Shopware\Core\Checkout\Promotion\Cart\PromotionCollector', 'PIN_MANUAL_PROMOTIONS', 'Shopware\Core\Checkout\CheckoutPermissions', 'PIN_MANUAL_PROMOTIONS'),
            new RenameClassAndConstFetch('Shopware\Core\Checkout\Promotion\Cart\PromotionCollector', 'PIN_AUTOMATIC_PROMOTIONS', 'Shopware\Core\Checkout\CheckoutPermissions', 'PIN_AUTOMATIC_PROMOTIONS'),

            new RenameClassAndConstFetch('Shopware\Core\Content\Product\Cart\ProductCartProcessor', 'ALLOW_PRODUCT_PRICE_OVERWRITES', 'Shopware\Core\Checkout\CheckoutPermissions', 'ALLOW_PRODUCT_PRICE_OVERWRITES'),
            new RenameClassAndConstFetch('Shopware\Core\Content\Product\Cart\ProductCartProcessor', 'ALLOW_PRODUCT_LABEL_OVERWRITES', 'Shopware\Core\Checkout\CheckoutPermissions', 'ALLOW_PRODUCT_LABEL_OVERWRITES'),
            new RenameClassAndConstFetch('Shopware\Core\Content\Product\Cart\ProductCartProcessor', 'SKIP_PRODUCT_RECALCULATION', 'Shopware\Core\Checkout\CheckoutPermissions', 'SKIP_PRODUCT_RECALCULATION'),
            new RenameClassAndConstFetch('Shopware\Core\Content\Product\Cart\ProductCartProcessor', 'SKIP_PRODUCT_STOCK_VALIDATION', 'Shopware\Core\Checkout\CheckoutPermissions', 'SKIP_PRODUCT_STOCK_VALIDATION'),
            new RenameClassAndConstFetch('Shopware\Core\Content\Product\Cart\ProductCartProcessor', 'KEEP_INACTIVE_PRODUCT', 'Shopware\Core\Checkout\CheckoutPermissions', 'KEEP_INACTIVE_PRODUCT'),
            new RenameClassAndConstFetch('Shopware\Core\SalesChannelRequest', 'ATTRIBUTE_SALES_CHANNEL_MAINTENANCE_IP_WHITLELIST', 'Shopware\Core\SalesChannelRequest', 'ATTRIBUTE_SALES_CHANNEL_MAINTENANCE_IP_ALLOWLIST'),
            new RenameClassAndConstFetch('Shopware\Core\Framework\Adapter\Kernel\HttpCacheKernel', 'MAINTENANCE_WHITELIST_HEADER', 'Shopware\Core\Framework\Adapter\Kernel\HttpCacheKernel', 'MAINTENANCE_ALLOWLIST_HEADER'),
            new RenameClassAndConstFetch('Shopware\Storefront\Theme\ThemeLifecycleHandler', 'STATE_SKIP_THEME_COMPILATION', 'Shopware\Core\Framework\App\Lifecycle\AbstractAppLifecycle', 'STATE_SKIP_THEME_COMPILATION'),
        ],
    );

    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('Shopware\Core\System\SystemConfig\Service\ConfigurationService', 'getConfiguration', 'getSystemConfigDefinition'),
        new MethodCallRename('Shopware\Core\System\SystemConfig\Service\ConfigurationService', 'getResolvedConfiguration', 'getResolvedSystemConfigDefinition'),
        new MethodCallRename('Shopware\Core\System\SalesChannel\SalesChannelEntity', 'getMaintenanceIpWhitelist', 'getMaintenanceIpAllowlist'),
        new MethodCallRename('Shopware\Core\System\SalesChannel\SalesChannelEntity', 'setMaintenanceIpWhitelist', 'setMaintenanceIpAllowlist'),
        new MethodCallRename('Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerPasswordMatches', 'getContext', 'getSalesChannelContext'),
        new MethodCallRename('Shopware\Core\Framework\Script\Api\ResponseCacheConfiguration', 'maxAge', 'sharedMaxAge'),
    ]);

    $rectorConfig->ruleWithConfiguration(RenamePropertyRector::class, [
        new RenameProperty('Shopware\Core\System\SalesChannel\SalesChannelContext', 'permisionsLocked', 'permissionsLocked'),
        new RenameProperty('Shopware\Core\System\SalesChannel\SalesChannelEntity', 'maintenanceIpWhitelist', 'maintenanceIpAllowlist'),
    ]);
};
