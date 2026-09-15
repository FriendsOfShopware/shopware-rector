<?php

declare(strict_types=1);

namespace Frosh\Rector\Set;

use Frosh\Rector\Migration\v65\Shopware65Migration;
use Frosh\Rector\Migration\v66\Shopware66Migration;
use Frosh\Rector\Migration\v67\Shopware67Migration;
use Frosh\Rector\Migration\v68\CheckoutPermissionsMigration;
use Frosh\Rector\Migration\v68\ProductStreamBuilderInterfaceMigration;
use Frosh\Rector\Migration\v68\Shopware68Migration;
use Frosh\Rector\Rule\BCChange\BCChangeRector;
use Frosh\Rector\Rule\v67\AddEntityNameToEntityExtension;
use Frosh\Rector\Rule\v67\AddLoggerToScheduledTaskConstructorRector;
use Frosh\Rector\Rule\v68\CartBehaviorIsRecalculationRector;
use Frosh\Rector\Rule\v68\EntitySearchResultGetEntitiesRector;
use Frosh\Rector\Rule\v68\ProductStreamBuilderBuildFiltersToEnrichCriteriaRector;
use Frosh\Rector\Version\ShopwareVersionRange;
use Frosh\Rector\Version\VersionAwareMigrationInterface;
use Frosh\Rector\Version\VersionAwareRectorInterface;
use Rector\Configuration\RectorConfigBuilder;
use Rector\Contract\Rector\ConfigurableRectorInterface;

final class ShopwareSet
{
    /** @var list<class-string<VersionAwareRectorInterface>> */
    private const VERSION_AWARE_RECTORS = [
        AddEntityNameToEntityExtension::class,
        AddLoggerToScheduledTaskConstructorRector::class,
        EntitySearchResultGetEntitiesRector::class,
        CartBehaviorIsRecalculationRector::class,
        ProductStreamBuilderBuildFiltersToEnrichCriteriaRector::class,
    ];

    /** @var list<class-string<VersionAwareMigrationInterface>> */
    private const VERSION_AWARE_MIGRATIONS = [
        Shopware65Migration::class,
        Shopware66Migration::class,
        Shopware67Migration::class,
        Shopware68Migration::class,
        CheckoutPermissionsMigration::class,
        ProductStreamBuilderInterfaceMigration::class,
    ];

    public static function forVersionRange(
        RectorConfigBuilder $rectorConfig,
        string $minimumVersion,
        string $targetVersion,
    ): RectorConfigBuilder {
        $versions = new ShopwareVersionRange($minimumVersion, $targetVersion);
        $bcChanges = BCChangeSet::forVersionRange($versions->minimum, $versions->target);

        $rectorConfig
            ->withConfiguredRule(BCChangeRector::class, $bcChanges)
        ;

        foreach (self::VERSION_AWARE_RECTORS as $versionAwareRector) {
            if (!$versionAwareRector::isActive($versions)) {
                continue;
            }

            $configuration = $versionAwareRector::configuration($versions);
            if ($configuration === []) {
                $rectorConfig->withRules([$versionAwareRector]);
            } else {
                if (!is_a($versionAwareRector, ConfigurableRectorInterface::class, true)) {
                    throw new \LogicException(sprintf('Version-aware Rector "%s" returns configuration but is not configurable.', $versionAwareRector));
                }

                $rectorConfig->withConfiguredRule($versionAwareRector, $configuration);
            }
        }

        foreach (self::VERSION_AWARE_MIGRATIONS as $versionAwareMigration) {
            if ($versionAwareMigration::isActive($versions)) {
                $versionAwareMigration::register($rectorConfig);
            }
        }

        return $rectorConfig;
    }
}
