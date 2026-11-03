<?php

use Shopware\Storefront\Framework\AffiliateTracking\AffiliateTrackingListener;

$affiliateCode = $session->get(AffiliateTrackingListener::AFFILIATE_CODE_KEY);
$campaignCode = $session->get(AffiliateTrackingListener::CAMPAIGN_CODE_KEY);
-----
<?php

use Shopware\Core\Checkout\Order\SalesChannel\OrderService;
use Shopware\Storefront\Framework\AffiliateTracking\AffiliateTrackingListener;

$affiliateCode = $session->get(OrderService::AFFILIATE_CODE_KEY);
$campaignCode = $session->get(OrderService::CAMPAIGN_CODE_KEY);
