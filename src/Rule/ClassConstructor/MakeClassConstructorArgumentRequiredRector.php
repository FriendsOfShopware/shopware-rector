<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\ClassConstructor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\NullType;
use PHPStan\Type\StringType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class MakeClassConstructorArgumentRequiredRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var MakeClassConstructorArgumentRequired[]
     */
    protected array $configuration;

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('NAME', [
            new ConfiguredCodeSample(
                <<<'PHP'
class Foo {
    public function __construct(array $foo = [])
    {
    }
}
PHP
                ,
                <<<'PHP'
class Foo {
    public function __construct(array $foo)
    {
    }
}
PHP,
                [new MakeClassConstructorArgumentRequired('Foo', 0, new ArrayType(new StringType(), new StringType()))]
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [
            Node\Stmt\ClassMethod::class,
            Node\Expr\New_::class,
        ];
    }

    /**
     * @param Node\Stmt\ClassMethod|Node\Expr\New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Node\Stmt\ClassMethod) {
            return $this->rebuildClassMethod($node);
        }

        return $this->rebuildNew($node);
    }

    public function configure(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    private function rebuildClassMethod(Node\Stmt\ClassMethod $node): ?Node
    {
        if (!$this->isName($node, '__construct')) {
            return null;
        }

        $class = $this->betterNodeFinder->findParentType($node, Class_::class);

        if ($class === null) {
            return null;
        }

        $hasModified = false;

        foreach ($this->configuration as $config) {
            if (!$this->isObjectType($class, $config->getClassObject())) {
                continue;
            }

            if (!isset($node->params[$config->getPosition()])) {
                continue;
            }

            $node->params[$config->getPosition()]->default = null;

            $hasModified = true;
        }

        if ($hasModified) {
            return $node;
        }

        return null;
    }

    private function rebuildNew(Node\Expr\New_ $node)
    {
        $hasModified = false;

        foreach ($this->configuration as $config) {
            if (!$this->isObjectType($node->class, $config->getClassObject())) {
                continue;
            }

            if (isset($node->args[$config->getPosition()])) {
                continue;
            }

            if ($config->getDefault()) {
                $arg = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($config->getDefault(), TypeKind::ANY);

                if ($config->getDefault() instanceof NullType) {
                    $arg = new Node\Expr\ConstFetch($arg);
                }

                $node->args[$config->getPosition()] = new Node\Arg($arg);
            }

            $hasModified = true;
        }

        if ($hasModified) {
            return $node;
        }

        return null;
    }
}
