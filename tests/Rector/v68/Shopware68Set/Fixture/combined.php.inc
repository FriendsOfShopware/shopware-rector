<?php

function upgrade(
    \Shopware\Core\System\SystemConfig\Service\ConfigurationService $configuration,
    \Shopware\Core\System\SalesChannel\SalesChannelEntity $salesChannel,
    \Shopware\Core\System\SalesChannel\SalesChannelContext $context,
    \Shopware\Core\Content\Flow\Events\BeforeLoadStorableFlowDataEvent $event,
    \Shopware\Core\Content\Product\SalesChannel\Review\ProductReviewResult $reviews,
): void {
    $configuration->getConfiguration('Plugin');
    $configuration->getResolvedConfiguration('Plugin');
    $salesChannel->getMaintenanceIpWhitelist();
    $salesChannel->setMaintenanceIpWhitelist([]);
    $locked = $context->permisionsLocked;
    $event->getCriteria();
    $reviews->first();
    $permission = \Shopware\Core\Checkout\Cart\Delivery\DeliveryProcessor::SKIP_DELIVERY_PRICE_RECALCULATION;
    $theme = \Shopware\Storefront\Theme\ThemeLifecycleHandler::STATE_SKIP_THEME_COMPILATION;
    $attribute = \Shopware\Core\SalesChannelRequest::ATTRIBUTE_SALES_CHANNEL_MAINTENANCE_IP_WHITLELIST;
    $header = \Shopware\Core\Framework\Adapter\Kernel\HttpCacheKernel::MAINTENANCE_WHITELIST_HEADER;
    new \Shopware\Core\Framework\DataAbstractionLayer\Dbal\Exception\UnmappedFieldException('test', 'test');
}
-----
<?php

function upgrade(
    \Shopware\Core\System\SystemConfig\Service\ConfigurationService $configuration,
    \Shopware\Core\System\SalesChannel\SalesChannelEntity $salesChannel,
    \Shopware\Core\System\SalesChannel\SalesChannelContext $context,
    \Shopware\Core\Content\Shared\MailFlow\Event\MailFlowDataCriteriaEvent $event,
    \Shopware\Core\Content\Product\SalesChannel\Review\ProductReviewResult $reviews,
): void {
    $configuration->getSystemConfigDefinition('Plugin');
    $configuration->getResolvedSystemConfigDefinition('Plugin');
    $salesChannel->getMaintenanceIpAllowlist();
    $salesChannel->setMaintenanceIpAllowlist([]);
    $locked = $context->permissionsLocked;
    $event->criteria;
    $reviews->getEntities()->first();
    $permission = \Shopware\Core\Checkout\CheckoutPermissions::SKIP_DELIVERY_PRICE_RECALCULATION;
    $theme = \Shopware\Core\Framework\App\Lifecycle\AbstractAppLifecycle::STATE_SKIP_THEME_COMPILATION;
    $attribute = \Shopware\Core\SalesChannelRequest::ATTRIBUTE_SALES_CHANNEL_MAINTENANCE_IP_ALLOWLIST;
    $header = \Shopware\Core\Framework\Adapter\Kernel\HttpCacheKernel::MAINTENANCE_ALLOWLIST_HEADER;
    new \Shopware\Core\Framework\DataAbstractionLayer\Exception\UnmappedFieldException('test', 'test');
}
