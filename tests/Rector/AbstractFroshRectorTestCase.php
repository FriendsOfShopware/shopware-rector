<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractFroshRectorTestCase extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test($fileInfo): void
    {
        $this->doTestFile($fileInfo);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(dirname((new \ReflectionClass(static::class))->getFileName()) . '/Fixture');
    }

    public function provideConfigFilePath(): string
    {
        return dirname((new \ReflectionClass(static::class))->getFileName()) . '/config/configured_rule.php';
    }
}
