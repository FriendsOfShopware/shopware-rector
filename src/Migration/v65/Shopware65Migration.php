<?php

declare(strict_types=1);

namespace Frosh\Rector\Migration\v65;

use Frosh\Rector\Version\ShopwareVersionRange;
use Frosh\Rector\Version\VersionAwareMigrationInterface;
use Rector\Configuration\RectorConfigBuilder;

final class Shopware65Migration implements VersionAwareMigrationInterface
{
    public static function isActive(ShopwareVersionRange $versions): bool
    {
        return $versions->minimumIsAtLeast('6.5.0');
    }

    public static function register(RectorConfigBuilder $rectorConfig): void
    {
        $rectorConfig->withSets([
            __DIR__ . '/../../../config/v6.5/flysystem-v3.php',
            __DIR__ . '/../../../config/v6.5/renaming.php',
            __DIR__ . '/../../../config/v6.5/typehints.php',
            __DIR__ . '/../../../config/v6.5/rules.php',
        ]);
    }
}
