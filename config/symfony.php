<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\CodeQuality\Rector\Class_\EventSubscriberToAttributeRector;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        SymfonySetList::COMPOSER_BASED,
    ]);

    // Subscribers are registered via the kernel.event_subscriber tag.
    if (class_exists(EventSubscriberToAttributeRector::class)) {
        $rectorConfig->skip([
            EventSubscriberToAttributeRector::class,
        ]);
    }
};
