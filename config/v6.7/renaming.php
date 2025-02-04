<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/../config.php');

    $rectorConfig->ruleWithConfiguration(
        RenameClassConstFetchRector::class,
        [
            new RenameClassAndConstFetch('Shopware\Core\Content\MailTemplate\Subscriber\MailSendSubscriberConfig', 'MAIL_CONFIG_EXTENSION', 'Shopware\Core\Content\Flow\Dispatching\Action\SendMailAction', 'MAIL_CONFIG_EXTENSION'),
            new RenameClassAndConstFetch('Shopware\Core\Content\MailTemplate\Subscriber\MailSendSubscriberConfig', 'ACTION_NAME', 'Shopware\Core\Content\Flow\Dispatching\Action\SendMailAction', 'ACTION_NAME'),

            new RenameClassAndConstFetch('Shopware\Core\Content\MailTemplate\MailTemplateActions', 'MAIL_TEMPLATE_MAIL_SEND_ACTION', 'Shopware\Core\Content\Flow\Dispatching\Action\SendMailAction', 'ACTION_NAME'),

            new RenameClassAndConstFetch('Shopware\Core\Framework\Adapter\Cache\Http\CacheResponseSubscriber', 'STATE_LOGGED_IN', 'Shopware\Core\Framework\Adapter\Cache\CacheStateSubscriber', 'STATE_LOGGED_IN'),
            new RenameClassAndConstFetch('Shopware\Core\Framework\Adapter\Cache\Http\CacheResponseSubscriber', 'STATE_CART_FILLED', 'Shopware\Core\Framework\Adapter\Cache\CacheStateSubscriber', 'STATE_CART_FILLED'),

            new RenameClassAndConstFetch('Shopware\Core\Framework\Adapter\Cache\Http\CacheResponseSubscriber', 'CURRENCY_COOKIE', 'Shopware\Core\Framework\Adapter\Cache\Http\HttpCacheKeyGenerator', 'CURRENCY_COOKIE'),
            new RenameClassAndConstFetch('Shopware\Core\Framework\Adapter\Cache\Http\CacheResponseSubscriber', 'CONTEXT_CACHE_COOKIE', 'Shopware\Core\Framework\Adapter\Cache\Http\HttpCacheKeyGenerator', 'CONTEXT_CACHE_COOKIE'),
            new RenameClassAndConstFetch('Shopware\Core\Framework\Adapter\Cache\Http\CacheResponseSubscriber', 'SYSTEM_STATE_COOKIE', 'Shopware\Core\Framework\Adapter\Cache\Http\HttpCacheKeyGenerator', 'SYSTEM_STATE_COOKIE'),
            new RenameClassAndConstFetch('Shopware\Core\Framework\Adapter\Cache\Http\CacheResponseSubscriber', 'INVALIDATION_STATES_HEADER', 'Shopware\Core\Framework\Adapter\Cache\Http\HttpCacheKeyGenerator', 'INVALIDATION_STATES_HEADER'),
        ],
    );
};
