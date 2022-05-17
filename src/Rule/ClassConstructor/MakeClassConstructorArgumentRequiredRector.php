<?php

namespace Frosh\Rector\Rule\ClassConstructor;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function var_dump;

class MakeClassConstructorArgumentRequiredRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var MakeClassConstructorArgumentRequired[]
     */
    protected array $configuration;

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('NAME', [
            new CodeSample(
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
    public function __construct(array $foo = [])
    {
    }
}
PHP
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [
            Node\Stmt\ClassMethod::class,
            Node\Expr\New_::class
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
                $node->args[$config->getPosition()] = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($config->getDefault(), TypeKind::ANY());
            }

            $hasModified = true;
        }

        if ($hasModified) {
            return $node;
        }

        return null;
    }
}