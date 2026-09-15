<?php

declare(strict_types=1);

namespace Frosh\Rector\Migration\v68;

use Frosh\Rector\Version\ShopwareVersionRange;
use Frosh\Rector\Version\VersionAwareMigrationInterface;
use Rector\Configuration\RectorConfigBuilder;

final class Shopware68Migration implements VersionAwareMigrationInterface
{
    public static function isActive(ShopwareVersionRange $versions): bool
    {
        return $versions->minimumIsAtLeast('6.8.0');
    }

    public static function register(RectorConfigBuilder $rectorConfig): void
    {
        $rectorConfig->withSets([
            __DIR__ . '/../../../config/v6.8/renaming.php',
        ]);
    }
}
