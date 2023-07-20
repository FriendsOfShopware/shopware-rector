<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\ClassConstructor;

use function array_values;
use Frosh\Rector\Rule\ClassMethod\AddArgumentToClassWithoutDefault;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class RemoveArgumentFromClassConstructRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var AddArgumentToClassWithoutDefault[]
     */
    protected array $configuration = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'This Rector removes an argument in the defined class construct.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass($example);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass();
CODE_SAMPLE
                    ,
                    [
                        new RemoveArgumentFromClassConstruct('SomeExampleClass', 0),
                    ]
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [
            New_::class,
            ClassMethod::class,
        ];
    }

    /**
     * @param New_|ClassMethod $node
     */
    public function refactor(Node $node)
    {
        if ($node instanceof New_) {
            return $this->rebuildNew($node);
        }

        return $this->rebuildConstructor($node);
    }

    /**
     * @param AddArgumentToClassWithoutDefault[] $configuration
     * @return void
     */
    public function configure(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    private function rebuildNew(New_ $node): ?Node
    {
        $hasChanged = false;

        foreach ($this->configuration as $config) {
            if (!$this->isObjectType($node->class, $config->getObjectType())) {
                continue;
            }

            $args = $node->getArgs();
            if (!isset($args[$config->getPosition()])) {
                continue;
            }
            unset($node->args[$config->getPosition()]);
            $hasChanged = true;
        }

        if ($hasChanged) {
            $node->args = array_values($node->args);

            return $node;
        }

        return null;
    }

    private function rebuildConstructor(ClassMethod $node): ?Node
    {
        if (!$this->isName($node, '__construct')) {
            return null;
        }

        $class = $this->betterNodeFinder->findParentType($node, Class_::class);

        if ($class === null) {
            return null;
        }

        $hasChanged = false;

        foreach ($this->configuration as $config) {
            if (!$this->isObjectType($class, $config->getObjectType())) {
                continue;
            }

            $args = $node->params;
            if (!isset($args[$config->getPosition()])) {
                continue;
            }
            unset($node->params[$config->getPosition()]);
            $hasChanged = true;
        }

        if ($hasChanged) {
            $node->params = array_values($node->params);

            return $node;
        }

        return null;
    }
}
