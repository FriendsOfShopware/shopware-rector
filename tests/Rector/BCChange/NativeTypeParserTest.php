<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Rector\BCChange;

use Frosh\Rector\Rule\BCChange\NativeTypeParser;
use PhpParser\PrettyPrinter\Standard;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** @internal */
#[CoversClass(NativeTypeParser::class)]
final class NativeTypeParserTest extends TestCase
{
    #[DataProvider('provideTypes')]
    public function testQualifiesClassNames(string $type, string $expected): void
    {
        self::assertSame($expected, (new Standard())->prettyPrint([NativeTypeParser::parse($type)]));
    }

    public static function provideTypes(): \Iterator
    {
        yield 'native union' => ['int|string', 'int|string'];
        yield 'nullable class' => ['?DateTimeInterface', '?\DateTimeInterface'];
        yield 'intersection' => ['Countable&Traversable', '\Countable&\Traversable'];
        yield 'DNF type' => ['(Countable&Traversable)|null', '(\Countable&\Traversable)|null'];
        yield 'relative class types' => ['self|static|parent', 'self|static|parent'];
    }
}
