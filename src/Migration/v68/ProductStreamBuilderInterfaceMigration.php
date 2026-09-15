<?php

declare(strict_types=1);

namespace Frosh\Rector\Migration\v68;

use Frosh\Rector\Rule\Class_\InterfaceReplacedWithAbstractClass;
use Frosh\Rector\Rule\Class_\InterfaceReplacedWithAbstractClassRector;
use Frosh\Rector\Version\ShopwareVersionRange;
use Frosh\Rector\Version\VersionAwareMigrationInterface;
use Rector\Configuration\RectorConfigBuilder;

final class ProductStreamBuilderInterfaceMigration implements VersionAwareMigrationInterface
{
    public static function isActive(ShopwareVersionRange $versions): bool
    {
        return $versions->minimumIsAtLeast('6.7.13') && $versions->targetIsAtLeast('6.8.0');
    }

    public static function register(RectorConfigBuilder $rectorConfig): void
    {
        $rectorConfig->withConfiguredRule(InterfaceReplacedWithAbstractClassRector::class, [
            new InterfaceReplacedWithAbstractClass(
                'Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface',
                '\Shopware\Core\Content\ProductStream\Service\AbstractProductStreamBuilder',
            ),
        ]);
    }
}
