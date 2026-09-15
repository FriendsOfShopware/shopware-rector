<?php

declare(strict_types=1);

namespace Frosh\Rector\Set;

use Frosh\Rector\Rule\BCChange\BCChangeRector;
use Rector\Configuration\RectorConfigBuilder;

final class ShopwareSet
{
    /** @var array<string, list<string>> */
    private const TARGET_ONLY_SETS = [
        '6.5.0' => [
            __DIR__ . '/../../config/v6.5/flysystem-v3.php',
            __DIR__ . '/../../config/v6.5/renaming.php',
            __DIR__ . '/../../config/v6.5/typehints.php',
            __DIR__ . '/../../config/v6.5/rules.php',
        ],
        '6.6.0' => [
            __DIR__ . '/../../config/v6.6/renaming.php',
            __DIR__ . '/../../config/v6.6/exceptions.php',
        ],
        '6.7.0' => [
            __DIR__ . '/../../config/v6.7/renaming.php',
            __DIR__ . '/../../config/v6.7/return-types.php',
            __DIR__ . '/../../config/v6.7/scheduled-task-logger.php',
        ],
        '6.8.0' => [
            __DIR__ . '/../../config/v6.8/renaming.php',
        ],
    ];

    /** @var list<array{effectiveVersion: string, availableFrom: string, set: string}> */
    private const BRIDGE_SETS = [
        [
            'effectiveVersion' => '6.7.0',
            'availableFrom' => '6.6.0',
            'set' => __DIR__ . '/../../config/v6.7/scheduled-task-logger.php',
        ],
        [
            'effectiveVersion' => '6.8.0',
            'availableFrom' => '6.7.0',
            'set' => __DIR__ . '/../../config/v6.8/bridge-6.7.0.php',
        ],
        [
            'effectiveVersion' => '6.8.0',
            'availableFrom' => '6.7.2',
            'set' => __DIR__ . '/../../config/v6.8/bridge-6.7.2.php',
        ],
        [
            'effectiveVersion' => '6.8.0',
            'availableFrom' => '6.7.13',
            'set' => __DIR__ . '/../../config/v6.8/bridge-6.7.13.php',
        ],
    ];

    public static function forVersionRange(
        RectorConfigBuilder $rectorConfig,
        string $minimumVersion,
        string $targetVersion,
    ): RectorConfigBuilder {
        $minimumVersion = ltrim($minimumVersion, 'v');
        $targetVersion = ltrim($targetVersion, 'v');
        $bcChanges = BCChangeSet::forVersionRange($minimumVersion, $targetVersion);
        $sets = [];

        foreach (self::TARGET_ONLY_SETS as $effectiveVersion => $targetOnlySets) {
            if (version_compare($minimumVersion, $effectiveVersion, '>=')) {
                array_push($sets, ...$targetOnlySets);
            }
        }

        foreach (self::BRIDGE_SETS as $bridgeSet) {
            if (version_compare($minimumVersion, $bridgeSet['effectiveVersion'], '<')
                && version_compare($targetVersion, $bridgeSet['effectiveVersion'], '>=')
                && version_compare($minimumVersion, $bridgeSet['availableFrom'], '>=')
            ) {
                $sets[] = $bridgeSet['set'];
            }
        }

        $rectorConfig
            ->withSets($sets)
            ->withConfiguredRule(BCChangeRector::class, $bcChanges)
        ;

        if (version_compare($targetVersion, '6.7.0', '>=') && version_compare($minimumVersion, '6.5.0', '>=')) {
            $rectorConfig->withSets([
                version_compare($minimumVersion, '6.7.0', '<')
                    ? __DIR__ . '/../../config/v6.7/entity-extension-bridge.php'
                    : __DIR__ . '/../../config/v6.7/entity-extension-target.php',
            ]);
        }

        return $rectorConfig;
    }
}
