<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Generator;

use Frosh\Rector\Generator\BCChangeConfigGenerator;
use Frosh\Rector\Rule\BCChange\BCChangeConfiguration;
use Frosh\Rector\Rule\BCChange\ValueObject\AddOptionalParameter;
use Frosh\Rector\Rule\BCChange\ValueObject\AddRequiredParameter;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeParameterDefault;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeParameterName;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeParameterType;
use Frosh\Rector\Rule\BCChange\ValueObject\ChangeReturnType;
use Frosh\Rector\Rule\BCChange\ValueObject\RemoveParameter;
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

        self::assertEquals([
            new ChangeParameterDefault('v6.8.0', BCChangeFixture::class, 'changeDefault', 0, 'enabled', false),
            new AddOptionalParameter('v6.8.0', BCChangeFixture::class, 'load', 1, 'fresh', 'bool', false),
            new ChangeParameterType('v6.8.0', BCChangeFixture::class, 'load', 'id', 'string', 'int|string'),
            new ChangeReturnType('v6.8.0', BCChangeFixture::class, 'load', 'object', 'static'),
            new RemoveParameter('v6.8.0', BCChangeFixture::class, 'remove', 1, 'obsolete'),
            new ChangeParameterName('v6.8.0', BCChangeFixture::class, 'rename', 2, 'third', 'renamed', [
                ['name' => 'required', 'hasDefault' => false],
                ['name' => 'optional', 'hasDefault' => true, 'default' => false],
            ]),
            new AddRequiredParameter('v6.8.0', BCChangeFixture::class, 'requireParameter', 1, 'context', 'object'),
        ], $changes);
    }

    public function testReplacesOnlyTheGeneratedVersion(): void
    {
        $generator = new BCChangeConfigGenerator();
        $old = new RemoveParameter('v6.7.0', 'Example', 'run', 0, 'old');
        $stale = new RemoveParameter('v6.8.0', 'Example', 'run', 0, 'stale');
        $replacement = new RemoveParameter('v6.8.0', 'Example', 'run', 0, 'replacement');

        self::assertEquals([$old, $replacement], $generator->replaceVersion([$old, $stale], [$replacement], 'v6.8.0'));
    }

    public function testRendersTypedConfiguration(): void
    {
        $configuration = (new BCChangeConfigGenerator())->render([
            new RemoveParameter('v6.8.0', 'Example', 'run', 0, 'obsolete'),
        ]);

        self::assertStringContainsString('return new ' . BCChangeConfiguration::class . '(', $configuration);
        self::assertStringContainsString('new ' . RemoveParameter::class . '(', $configuration);
    }
}
