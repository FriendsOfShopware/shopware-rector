<?php

declare(strict_types=1);

namespace Frosh\Rector\Version;

use Rector\Configuration\RectorConfigBuilder;

interface VersionAwareMigrationInterface
{
    public static function isActive(ShopwareVersionRange $versions): bool;

    public static function register(RectorConfigBuilder $rectorConfig): void;
}
