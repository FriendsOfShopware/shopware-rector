<?php declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\ClassMethod\ChangeReturnTypeOfClassMethodRector;

use Frosh\Rector\Tests\Rector\AbstractFroshRectorTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @internal
 */
#[CoversNothing]
final class ChangeReturnTypeOfClassMethodRectorTest extends AbstractFroshRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $fileInfo): void
    {
        // Rector only prints a warning when a rule reports a change without changing the file,
        // which makes `rector process --dry-run` fail with an empty diff.
        $this->expectOutputString('');

        parent::test($fileInfo);
    }
}
