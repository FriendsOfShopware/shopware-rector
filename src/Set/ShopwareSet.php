<?php

declare(strict_types=1);

namespace Frosh\Rector\Set;

use Frosh\Rector\Rule\v67\AddEntityNameToEntityExtension;
use Frosh\Rector\Rule\v67\AddLoggerToScheduledTaskConstructorRector;
use Frosh\Rector\Rule\v68\CartBehaviorIsRecalculationRector;
use Frosh\Rector\Rule\v68\EntitySearchResultGetEntitiesRector;
use Frosh\Rector\Rule\v68\ProductStreamBuilderBuildFiltersToEnrichCriteriaRector;
use Frosh\Rector\Version\ShopwareVersionRange;
use Frosh\Rector\Version\VersionAwareRectorInterface;
use Rector\Configuration\RectorConfigBuilder;
use Rector\Contract\Rector\ConfigurableRectorInterface;

final class ShopwareSet
{
    /** @var list<class-string<VersionAwareRectorInterface>> */
    private const VERSION_AWARE_RECTORS = [
        AddEntityNameToEntityExtension::class,
        AddLoggerToScheduledTaskConstructorRector::class,
    ];

    public static function forVersionRange(
        RectorConfigBuilder $rectorConfig,
        string $minimumVersion,
        string $targetVersion,
    ): RectorConfigBuilder {
        $versions = new ShopwareVersionRange($minimumVersion, $targetVersion);
        BCChangeSet::configure($rectorConfig, $versions->minimum, $versions->target);

        $sets = [];
        if ($versions->minimumIsAtLeast('6.5.0')) {
            array_push(
                $sets,
                __DIR__ . '/../../config/v6.5/flysystem-v3.php',
                __DIR__ . '/../../config/v6.5/renaming.php',
                __DIR__ . '/../../config/v6.5/typehints.php',
                __DIR__ . '/../../config/v6.5/rules.php',
            );
        }
        if ($versions->minimumIsAtLeast('6.6.0')) {
            $sets[] = __DIR__ . '/../../config/v6.6/renaming.php';
            $sets[] = __DIR__ . '/../../config/v6.6/exceptions.php';
        }
        if ($versions->minimumIsAtLeast('6.7.0')) {
            $sets[] = __DIR__ . '/../../config/v6.7/renaming.php';
            $sets[] = __DIR__ . '/../../config/v6.7/return-types.php';
        }
        if ($versions->minimumIsAtLeast('6.8.0')) {
            $sets[] = __DIR__ . '/../../config/v6.8/renaming.php';
        }
        if (EntitySearchResultGetEntitiesRector::isActive($versions)) {
            $sets[] = __DIR__ . '/../../config/v6.8/entity-search-result.php';
        }
        if (CartBehaviorIsRecalculationRector::isActive($versions)) {
            $sets[] = __DIR__ . '/../../config/v6.8/checkout-permissions.php';
        }
        if (ProductStreamBuilderBuildFiltersToEnrichCriteriaRector::isActive($versions)) {
            $sets[] = __DIR__ . '/../../config/v6.8/product-stream.php';
        }

        $rectorConfig->withSets($sets);

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

        return $rectorConfig;
    }
}
