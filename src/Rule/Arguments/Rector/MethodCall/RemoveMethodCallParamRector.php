<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\Arguments\Rector\MethodCall;

use Frosh\Rector\Rule\Arguments\ValueObject\RemoveMethodCallParam;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

final class RemoveMethodCallParamRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var RemoveMethodCallParam[]
     */
    private array $removeMethodCallParams = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove parameter of method call', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
                    final class SomeClass
                    {
                        public function run(Caller $caller)
                        {
                            $caller->process(1, 2);
                        }
                    }
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    final class SomeClass
                    {
                        public function run(Caller $caller)
                        {
                            $caller->process(1);
                        }
                    }
                    CODE_SAMPLE,
                [new RemoveMethodCallParam('Caller', 'process', 1)],
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }

        $hasChanged = false;

        foreach ($this->removeMethodCallParams as $removeMethodCallParam) {
            if (!$this->isName($node->name, $removeMethodCallParam->getMethodName())) {
                continue;
            }

            if (!$this->isCallerObjectType($node, $removeMethodCallParam)) {
                continue;
            }

            $args = $node->getArgs();
            if (!isset($args[$removeMethodCallParam->getParamPosition()])) {
                continue;
            }

            unset($node->args[$removeMethodCallParam->getParamPosition()]);
            $hasChanged = true;
        }

        if (!$hasChanged) {
            return null;
        }

        return $node;
    }

    /**
     * @param RemoveMethodCallParam[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsInstanceOf($configuration, RemoveMethodCallParam::class);
        $this->removeMethodCallParams = array_values($configuration);
    }

    private function isCallerObjectType(MethodCall|StaticCall $call, RemoveMethodCallParam $removeMethodCallParam): bool
    {
        if ($call instanceof MethodCall) {
            return $this->isObjectType($call->var, $removeMethodCallParam->getObjectType());
        }

        return $this->isObjectType($call->class, $removeMethodCallParam->getObjectType());
    }
}
