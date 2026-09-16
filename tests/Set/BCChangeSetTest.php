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
        $configuration = BCChangeSet::configuration();

        self::assertNotEmpty($configuration->changes);
        self::assertSame('v6.8.0', $configuration->changes[0]->version());
    }
}
