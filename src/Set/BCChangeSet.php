<?php

declare(strict_types=1);

namespace Frosh\Rector\Set;

use Frosh\Rector\Rule\BCChange\BCChangeConfiguration;
use Frosh\Rector\Version\ShopwareVersionRange;
use Rector\Configuration\RectorConfigBuilder;

final class BCChangeSet
{
    public static function configure(RectorConfigBuilder $rectorConfig, string $minimumVersion, string $targetVersion): RectorConfigBuilder
    {
        self::configuration()->register($rectorConfig, new ShopwareVersionRange($minimumVersion, $targetVersion));

        return $rectorConfig;
    }

    public static function configuration(): BCChangeConfiguration
    {
        /** @var BCChangeConfiguration $configuration */
        $configuration = require __DIR__ . '/../../config/bc-changes.php';

        return $configuration;
    }
}
