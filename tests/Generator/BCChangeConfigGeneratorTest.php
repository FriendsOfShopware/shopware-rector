<?php

declare(strict_types=1);

namespace Frosh\Rector\Tests\Generator;

use Frosh\Rector\Generator\BCChangeConfigGenerator;
use Frosh\Rector\Rule\BCChange\FutureCompatibleBCChangeRector;
use Frosh\Rector\Tests\Generator\Fixture\BCChangeFixture;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/** @internal */
#[CoversClass(BCChangeConfigGenerator::class)]
final class BCChangeConfigGeneratorTest extends TestCase
{
    public function testCollectsClassMovesForRequestedVersion(): void
    {
        $generator = new BCChangeConfigGenerator(__NAMESPACE__ . '\Fixture\BCChange\\');

        self::assertSame(['Legacy\Fixture' => BCChangeFixture::class], $generator->collectClassRenames([BCChangeFixture::class], 'v6.8.0'));
    }

    public function testRendersValuesWithoutChangingTheirMeaning(): void
    {
        $changes = [[
            'class' => BCChangeFixture::class,
            'method' => 'load',
            'kind' => FutureCompatibleBCChangeRector::ADD_OPTIONAL_PARAMETER,
            'position' => 1,
            'parameter' => 'options',
            'type' => 'array',
            'default' => [false, null, 1.5, "quote'", 'backslash\\', "line\nbreak"],
        ]];
        $renames = ['Legacy\Fixture' => BCChangeFixture::class];
        $nodes = (new ParserFactory())->createForNewestSupportedVersion()->parse((new BCChangeConfigGenerator())->render($changes, $renames));
        self::assertNotNull($nodes);
        $calls = (new NodeFinder())->findInstanceOf($nodes, MethodCall::class);

        self::assertCount(2, $calls);
        self::assertSame($changes, (new ConstExprEvaluator())->evaluateDirectly($calls[0]->getArgs()[1]->value));
        self::assertSame($renames, (new ConstExprEvaluator())->evaluateDirectly($calls[1]->getArgs()[1]->value));
    }

    public function testCommandDiscoversClassesWithoutAnOptimizedClassMap(): void
    {
        $filesystem = new Filesystem();
        $directory = sys_get_temp_dir() . '/bc-generator-' . bin2hex(random_bytes(8));
        $filesystem->dumpFile($directory . '/vendor/autoload.php', <<<'PHP'
            <?php
            spl_autoload_register(static function (string $class): void {
                if ($class === 'Shopware\\GeneratorFixture') {
                    require __DIR__ . '/../src/GeneratorFixture.php';
                }
            });
            PHP);
        $filesystem->dumpFile($directory . '/src/GeneratorFixture.php', <<<'PHP'
            <?php
            namespace Shopware;
            #[\Shopware\Core\Framework\Deprecation\BCChange\ClassMoved('v6.8.0', 'Shopware\\LegacyFixture')]
            final class GeneratorFixture
            {
                #[\Shopware\Core\Framework\Deprecation\BCChange\NewOptionalParameter('v6.8.0', 'enabled', 'bool', false)]
                public function load(): void {}
            }
            PHP);

        try {
            $process = new Process([\PHP_BINARY, __DIR__ . '/../../bin/generate-bc-change-config.php', $directory . '/vendor/autoload.php', 'v6.8.0', $directory . '/config.php']);
            $process->mustRun();
            $nodes = (new ParserFactory())->createForNewestSupportedVersion()->parse($filesystem->readFile($directory . '/config.php'));
            self::assertNotNull($nodes);
            $calls = (new NodeFinder())->findInstanceOf($nodes, MethodCall::class);
            self::assertCount(2, $calls);
            $changes = (new ConstExprEvaluator())->evaluateDirectly($calls[0]->getArgs()[1]->value);
            self::assertSame('Shopware\GeneratorFixture', $changes[0]['class']);
            self::assertSame('enabled', $changes[0]['parameter']);
            self::assertSame(['Shopware\LegacyFixture' => 'Shopware\GeneratorFixture'], (new ConstExprEvaluator())->evaluateDirectly($calls[1]->getArgs()[1]->value));
        } finally {
            $filesystem->remove($directory);
        }
    }

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
