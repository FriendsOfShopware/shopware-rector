<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Version;

use Frosh\Rector\Version\ShopwareVersionRange;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ShopwareVersionRange::class)]
final class ShopwareVersionRangeTest extends TestCase
{
    public function testComparesMinimumAndTargetIndependently(): void
    {
        $versions = new ShopwareVersionRange('v6.7.2', 'v6.8.0');

        self::assertTrue($versions->minimumIsAtLeast('6.7.2'));
        self::assertFalse($versions->minimumIsAtLeast('6.8.0'));
        self::assertTrue($versions->targetIsAtLeast('6.8.0'));
    }

    public function testRejectsInvertedRange(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ShopwareVersionRange('6.9.0', '6.8.0');
    }
}
