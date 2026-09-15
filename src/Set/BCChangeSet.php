<?php

declare(strict_types=1);

namespace Frosh\Rector\Set;

final class BCChangeSet
{
    /** @return array{minimumVersion: string, targetVersion: string, changes: list<array<string, mixed>>} */
    public static function forVersionRange(string $minimumVersion, string $targetVersion): array
    {
        $minimumVersion = ltrim($minimumVersion, 'v');
        $targetVersion = ltrim($targetVersion, 'v');

        if (version_compare($minimumVersion, $targetVersion, '>')) {
            throw new \InvalidArgumentException('The minimum Shopware version cannot be newer than the target version.');
        }

        /** @var list<array<string, mixed>> $changes */
        $changes = require __DIR__ . '/../../config/bc-changes.php';

        return [
            'minimumVersion' => $minimumVersion,
            'targetVersion' => $targetVersion,
            'changes' => $changes,
        ];
    }
}
