<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\PhpParser\Node\BetterNodeFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RuleContainerUnserializeRector extends AbstractRector
{
    public function __construct(private readonly BetterNodeFinder $betterNodeFinder) {}

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move rule container wakeup initialization to __unserialize()', [new CodeSample(
            'class Rules extends Container { public function __wakeup(): void { $this->init(); } }',
            'class Rules extends Container { public function __unserialize(array $data): void { parent::__unserialize($data); $this->init(); } }',
        )]);
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /** @param Class_ $node */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isObjectType($node, new ObjectType('Shopware\Core\Framework\Rule\Container\Container')) || $node->getMethod('__unserialize') !== null) {
            return null;
        }
        $wakeup = $node->getMethod('__wakeup');
        if ($wakeup === null || $wakeup->stmts === null || $wakeup->params !== []) {
            return null;
        }
        if ($this->betterNodeFinder->findFirst($wakeup, fn (Node $inner): bool => ($inner instanceof Variable && $this->isName($inner, 'data')) || ($inner instanceof StaticCall && $this->isName($inner->name, '__wakeup'))) !== null) {
            return null;
        }
        $wakeup->name = new Identifier('__unserialize');
        $wakeup->params = [new Param(new Variable('data'), type: new Identifier('array'))];
        $wakeup->returnType = new Identifier('void');
        array_unshift($wakeup->stmts, new Expression(new StaticCall(new Name('parent'), '__unserialize', [new Arg(new Variable('data'))])));

        return $node;
    }
}
