<?php declare(strict_types=1);

use Frosh\Rector\Rule\v65\AbstractMessageHandlerToMessageSubscriberRector;
use Frosh\Rector\Tests\Rector\v65\AbstractMessageHandlerToMessageSubscriberRector\Source\AbstractMessageHandler;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(AbstractMessageHandlerToMessageSubscriberRector::class);

    $rectorConfig->singleton(AbstractMessageHandler::class);
};
