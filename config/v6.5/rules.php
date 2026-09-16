<?php

declare(strict_types=1);

use Frosh\Rector\Rule\v65\AbstractMessageHandlerToMessageSubscriberRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');
    $rectorConfig->rule(AbstractMessageHandlerToMessageSubscriberRector::class);
};
