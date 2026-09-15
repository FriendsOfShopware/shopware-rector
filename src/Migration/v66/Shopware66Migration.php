<?php

declare(strict_types=1);

namespace Frosh\Rector\Migration\v66;

use Frosh\Rector\Version\ShopwareVersionRange;
use Frosh\Rector\Version\VersionAwareMigrationInterface;
use Rector\Configuration\RectorConfigBuilder;

final class Shopware66Migration implements VersionAwareMigrationInterface
{
    public static function isActive(ShopwareVersionRange $versions): bool
    {
        return $versions->minimumIsAtLeast('6.6.0');
    }

    public static function register(RectorConfigBuilder $rectorConfig): void
    {
        $rectorConfig->withSets([
            __DIR__ . '/../../../config/v6.6/renaming.php',
            __DIR__ . '/../../../config/v6.6/exceptions.php',
        ]);
    }
}
