<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/v6.8/renaming.php');
    $rectorConfig->import(__DIR__ . '/v6.8/entity-search-result.php');
    $rectorConfig->import(__DIR__ . '/v6.8/checkout-permissions.php');
    $rectorConfig->import(__DIR__ . '/v6.8/product-stream.php');
};
