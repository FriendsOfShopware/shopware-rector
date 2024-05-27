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
            new RenameClassAndConstFetch('Shopware\\Core\\Content\\MailTemplate\\Subscriber\\MailSendSubscriberConfig', 'MAIL_CONFIG_EXTENSION', 'Shopware\\Core\\Content\\Flow\\Dispatching\\Action\\SendMailAction', 'MAIL_CONFIG_EXTENSION'),
            new RenameClassAndConstFetch('Shopware\\Core\\Content\\MailTemplate\\Subscriber\\MailSendSubscriberConfig', 'ACTION_NAME', 'Shopware\\Core\\Content\\Flow\\Dispatching\\Action\\SendMailAction', 'ACTION_NAME'),
        ],
    );
};
