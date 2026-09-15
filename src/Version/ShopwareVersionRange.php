<?php

declare(strict_types=1);

namespace Frosh\Rector\Version;

final readonly class ShopwareVersionRange
{
    public string $minimum;

    public string $target;

    public function __construct(string $minimum, string $target)
    {
        $this->minimum = ltrim($minimum, 'v');
        $this->target = ltrim($target, 'v');

        if (version_compare($this->minimum, $this->target, '>')) {
            throw new \InvalidArgumentException('The minimum Shopware version cannot be newer than the target version.');
        }
    }

    public function minimumIsAtLeast(string $version): bool
    {
        return version_compare($this->minimum, $version, '>=');
    }

    public function targetIsAtLeast(string $version): bool
    {
        return version_compare($this->target, $version, '>=');
    }
}
