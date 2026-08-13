<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector;

use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

abstract class AbstractFroshRectorTestCase extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $fileInfo): void
    {
        $this->doTestFile($fileInfo);
    }

    public static function provideData(): \Iterator
    {
        return self::yieldFilesFromDirectory(dirname((new \ReflectionClass(static::class))->getFileName()) . '/Fixture');
    }

    public function provideConfigFilePath(): string
    {
        return dirname((new \ReflectionClass(static::class))->getFileName()) . '/config/configured_rule.php';
    }
}
