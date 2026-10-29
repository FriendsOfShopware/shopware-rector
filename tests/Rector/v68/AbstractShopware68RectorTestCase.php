<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\v68;

use Frosh\Rector\Tests\Rector\AbstractFroshRectorTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Filesystem\Filesystem;

/** @internal */
abstract class AbstractShopware68RectorTestCase extends AbstractFroshRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $fileInfo): void
    {
        parent::test($fileInfo);

        $contents = file_get_contents($fileInfo);
        self::assertIsString($contents);
        $parts = preg_split('/^-----\R/m', $contents, 2);
        self::assertIsArray($parts);
        if (count($parts) !== 2) {
            return;
        }

        // Process the expected output in a separate file, so stability is checked after reparsing too.
        $filesystem = new Filesystem();
        $fixture = $fileInfo . '.idempotent.php.inc';
        $filesystem->dumpFile($fixture, $parts[1]);
        try {
            $this->doTestFile($fixture);
        } finally {
            $filesystem->remove([$fixture, substr($fileInfo, 0, -strlen('.inc'))]);
        }
    }
}
