<?php

declare(strict_types=1);

namespace Frosh\Rector\Version;

interface VersionAwareRectorInterface
{
    public static function isActive(ShopwareVersionRange $versions): bool;

    /** @return array<string, mixed> */
    public static function configuration(ShopwareVersionRange $versions): array;
}
