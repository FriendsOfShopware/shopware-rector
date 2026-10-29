<?php

namespace LaterApis;

use Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerPasswordMatches;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\Script\Api\ResponseCacheConfiguration;
use Shopware\Core\System\SystemConfig\Exception\InvalidKeyException;
use Shopware\Core\System\SystemConfig\Service\ConfigurationService;

function configure(ConfigurationService $configuration, ResponseCacheConfiguration $cache): void
{
    $configuration->getConfiguration('Plugin');
    $cache->maxAge(60);
    new CustomerPasswordMatches(['message' => 'Invalid']);
    throw new InvalidKeyException('key');
}

class CustomDefinition extends EntityDefinition
{
    public function __construct()
    {
        parent::__construct();
    }
}
