<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Generator;

use Frosh\Rector\Generator\BCChangeConfigGenerator;
use Frosh\Rector\Rule\BCChange\BCChangeRector;
use Frosh\Rector\Tests\Generator\Fixture\BCChangeFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/** @internal */
#[CoversClass(BCChangeConfigGenerator::class)]
final class BCChangeConfigGeneratorTest extends TestCase
{
    public function testCollectsSupportedChangesForRequestedVersion(): void
    {
        $changes = (new BCChangeConfigGenerator(__NAMESPACE__ . '\Fixture\BCChange\\'))->collect([BCChangeFixture::class], 'v6.8.0');

        self::assertSame([
            [
                'version' => 'v6.8.0',
                'class' => BCChangeFixture::class,
                'method' => 'changeDefault',
                'kind' => BCChangeRector::EXPLICIT_CURRENT_DEFAULT,
                'position' => 0,
                'parameter' => 'enabled',
                'default' => false,
            ],
            [
                'version' => 'v6.8.0',
                'class' => BCChangeFixture::class,
                'method' => 'load',
                'kind' => BCChangeRector::ADD_OPTIONAL_PARAMETER,
                'position' => 1,
                'parameter' => 'fresh',
                'type' => 'bool',
                'default' => false,
            ],
            [
                'version' => 'v6.8.0',
                'class' => BCChangeFixture::class,
                'method' => 'load',
                'kind' => BCChangeRector::NARROW_RETURN_TYPE,
                'currentType' => 'object',
                'type' => 'static',
            ],
            [
                'version' => 'v6.8.0',
                'class' => BCChangeFixture::class,
                'method' => 'load',
                'kind' => BCChangeRector::WIDEN_PARAMETER_TYPE,
                'parameter' => 'id',
                'currentType' => 'string',
                'type' => 'int|string',
            ],
            [
                'version' => 'v6.8.0',
                'class' => BCChangeFixture::class,
                'method' => 'remove',
                'kind' => BCChangeRector::REMOVE_PARAMETER,
                'position' => 1,
                'parameter' => 'obsolete',
            ],
            [
                'version' => 'v6.8.0',
                'class' => BCChangeFixture::class,
                'method' => 'rename',
                'kind' => BCChangeRector::RENAME_PARAMETER,
                'position' => 2,
                'parameter' => 'third',
                'newName' => 'renamed',
                'parametersBefore' => [
                    [
                        'name' => 'required',
                        'hasDefault' => false,
                    ],
                    [
                        'name' => 'optional',
                        'hasDefault' => true,
                        'default' => false,
                    ],
                ],
            ],
            [
                'version' => 'v6.8.0',
                'class' => BCChangeFixture::class,
                'method' => 'requireParameter',
                'kind' => BCChangeRector::ADD_REQUIRED_PARAMETER,
                'position' => 1,
                'parameter' => 'context',
                'type' => 'object',
            ],
        ], $changes);
    }

    public function testReplacesOnlyTheGeneratedVersion(): void
    {
        $generator = new BCChangeConfigGenerator();

        self::assertSame([
            ['version' => 'v6.7.0', 'class' => 'Example', 'method' => 'run', 'kind' => 'old'],
            ['version' => 'v6.8.0', 'class' => 'Example', 'method' => 'run', 'kind' => 'replacement'],
        ], $generator->replaceVersion([
            ['version' => 'v6.7.0', 'class' => 'Example', 'method' => 'run', 'kind' => 'old'],
            ['version' => 'v6.8.0', 'class' => 'Example', 'method' => 'run', 'kind' => 'stale'],
        ], [
            ['version' => 'v6.8.0', 'class' => 'Example', 'method' => 'run', 'kind' => 'replacement'],
        ], 'v6.8.0'));
    }
}
