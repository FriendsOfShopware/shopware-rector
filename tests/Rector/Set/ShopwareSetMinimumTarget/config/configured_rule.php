<?php

declare(strict_types=1);

use Frosh\Rector\Set\ShopwareSet;
use Rector\Config\RectorConfig;

return ShopwareSet::forVersionRange(
    RectorConfig::configure()->withSets([
        __DIR__ . '/../../../../../config/config_test.php',
    ]),
    minimumVersion: '6.6.0',
    targetVersion: '6.7.0',
);
