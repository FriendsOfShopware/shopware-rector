<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Generator;

use Frosh\Rector\Generator\BCChangeConfigGenerator;
use Frosh\Rector\Rule\BCChange\FutureCompatibleBCChangeRector;
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
                'class' => BCChangeFixture::class,
                'method' => 'changeDefault',
                'kind' => FutureCompatibleBCChangeRector::EXPLICIT_CURRENT_DEFAULT,
                'position' => 0,
                'parameter' => 'enabled',
                'default' => false,
            ],
            [
                'class' => BCChangeFixture::class,
                'method' => 'load',
                'kind' => FutureCompatibleBCChangeRector::ADD_OPTIONAL_PARAMETER,
                'position' => 1,
                'parameter' => 'fresh',
                'type' => 'bool',
                'default' => false,
            ],
            [
                'class' => BCChangeFixture::class,
                'method' => 'load',
                'kind' => FutureCompatibleBCChangeRector::NARROW_RETURN_TYPE,
                'currentType' => 'object',
                'type' => 'static',
            ],
            [
                'class' => BCChangeFixture::class,
                'method' => 'load',
                'kind' => FutureCompatibleBCChangeRector::WIDEN_PARAMETER_TYPE,
                'parameter' => 'id',
                'currentType' => 'string',
                'type' => 'int|string',
            ],
        ], $changes);
    }
}
