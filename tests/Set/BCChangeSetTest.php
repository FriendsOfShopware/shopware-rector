<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Set;

use Frosh\Rector\Set\BCChangeSet;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/** @internal */
#[CoversClass(BCChangeSet::class)]
final class BCChangeSetTest extends TestCase
{
    public function testCreatesConfigurationForVersionRange(): void
    {
        $configuration = BCChangeSet::forVersionRange('v6.7.0', 'v6.8.0');

        self::assertSame('6.7.0', $configuration['minimumVersion']);
        self::assertSame('6.8.0', $configuration['targetVersion']);
        self::assertNotEmpty($configuration['changes']);
        self::assertSame('v6.8.0', $configuration['changes'][0]['version']);
    }

    public function testRejectsInvertedVersionRange(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        BCChangeSet::forVersionRange('6.9.0', '6.8.0');
    }
}
