<?php

declare(strict_types=1);

use Frosh\Rector\Set\ShopwareSetList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
    ]);

    $rectorConfig->importNames();

    $rectorConfig->sets([
        ShopwareSetList::SHOPWARE_6_5_0,
        ShopwareSetList::SHOPWARE_6_6_0,
        ShopwareSetList::SHOPWARE_6_6_4,
        ShopwareSetList::SHOPWARE_6_6_10,
        ShopwareSetList::SHOPWARE_6_7_0,
    ]);
};
