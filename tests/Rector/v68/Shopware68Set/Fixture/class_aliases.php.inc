<?php

new \Shopware\Administration\Notification\NotificationCollection();
new \Shopware\Administration\Notification\NotificationDefinition();
new \Shopware\Administration\Notification\NotificationEntity();
new \Shopware\Elasticsearch\Product\SearchConfigLoader();
new \Shopware\Core\Framework\Adapter\Console\ShopwareStyle();
-----
<?php

new \Shopware\Core\Framework\Notification\NotificationCollection();
new \Shopware\Core\Framework\Notification\NotificationDefinition();
new \Shopware\Core\Framework\Notification\NotificationEntity();
new \Shopware\Core\Framework\DataAbstractionLayer\Search\SearchConfigLoader();
new \Symfony\Component\Console\Style\SymfonyStyle();
