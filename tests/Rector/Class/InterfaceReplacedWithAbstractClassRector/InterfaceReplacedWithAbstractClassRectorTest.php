<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\Class\InterfaceReplacedWithAbstractClassRector;

use Frosh\Rector\Tests\Rector\AbstractFroshRectorTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class InterfaceReplacedWithAbstractClassRectorTest extends AbstractFroshRectorTestCase
{
    public function provideData(): \Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }
}
