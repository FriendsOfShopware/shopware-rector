<?php

use Shopware\Core\Framework\Plugin\Util\AssetService;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;

function moved(AssetService $assets, ShopwareStyle $style): void {}
-----
<?php

use Shopware\Core\Framework\Plugin\Util\AssetService;
use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;

function moved(\Shopware\Core\Framework\Adapter\Asset\AssetService $assets, \Symfony\Component\Console\Style\SymfonyStyle $style): void {}
