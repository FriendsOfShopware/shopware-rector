<?php

declare(strict_types=1);

namespace Frosh\Rector\Set;

use Frosh\Rector\Version\ShopwareVersionRange;

final class BCChangeSet
{
    /** @return array{minimumVersion: string, targetVersion: string, changes: list<array<string, mixed>>} */
    public static function forVersionRange(string $minimumVersion, string $targetVersion): array
    {
        $versions = new ShopwareVersionRange($minimumVersion, $targetVersion);

        /** @var list<array<string, mixed>> $changes */
        $changes = require __DIR__ . '/../../config/bc-changes.php';

        return [
            'minimumVersion' => $versions->minimum,
            'targetVersion' => $versions->target,
            'changes' => $changes,
        ];
    }
}
