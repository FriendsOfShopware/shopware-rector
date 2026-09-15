<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->import(__DIR__ . '/bridge-6.7.0.php');
    $rectorConfig->import(__DIR__ . '/bridge-6.7.2.php');
    $rectorConfig->import(__DIR__ . '/bridge-6.7.13.php');

    $rectorConfig->ruleWithConfiguration(
        RenameClassRector::class,
        [
            'Shopware\Core\Framework\Adapter\Console\ShopwareStyle' => 'Symfony\Component\Console\Style\SymfonyStyle',
        ],
    );

};
