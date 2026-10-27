<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class Shopware68DecoratorRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Implement new abstract contracts in forwarding Shopware decorators', [new CodeSample(
            'class Persister extends AbstractCartPersister { public function getDecorated(): AbstractCartPersister { return $this->inner; } }',
            'class Persister extends AbstractCartPersister { public function getDecorated(): AbstractCartPersister { return $this->inner; } public function exists(string $token, SalesChannelContext $context): bool { return $this->getDecorated()->exists($token, $context); } }',
        )]);
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /** @param Class_ $node */
    public function refactor(Node $node): ?Node
    {
        $decorated = $node->getMethod('getDecorated');
        // Storage implementations that throw here must implement their own operation.
        if ($decorated === null || count($decorated->stmts ?? []) !== 1 || !$decorated->stmts[0] instanceof Return_ || !$decorated->stmts[0]->expr instanceof PropertyFetch) {
            return null;
        }
        if ($this->isObjectType($node, new ObjectType('Shopware\Core\Checkout\Cart\AbstractCartPersister')) && $node->getMethod('exists') === null) {
            $node->stmts[] = new ClassMethod('exists', [
                'flags' => Class_::MODIFIER_PUBLIC,
                'params' => [new Param(new Variable('token'), type: new Identifier('string')), new Param(new Variable('context'), type: new FullyQualified('Shopware\Core\System\SalesChannel\SalesChannelContext'))],
                'returnType' => new Identifier('bool'),
                'stmts' => [new Return_(new MethodCall(new MethodCall(new Variable('this'), 'getDecorated'), 'exists', [new Arg(new Variable('token')), new Arg(new Variable('context'))]))],
            ]);

            return $node;
        }
        if ($this->isObjectType($node, new ObjectType('Shopware\Core\System\NumberRange\ValueGenerator\Pattern\IncrementStorage\AbstractIncrementStorage')) && $node->getMethod('increaseToAtLeast') === null) {
            $node->stmts[] = new ClassMethod('increaseToAtLeast', [
                'flags' => Class_::MODIFIER_PUBLIC,
                'params' => [new Param(new Variable('configurationId'), type: new Identifier('string')), new Param(new Variable('value'), type: new Identifier('int'))],
                'returnType' => new Identifier('void'),
                'stmts' => [new Expression(new MethodCall(new MethodCall(new Variable('this'), 'getDecorated'), 'increaseToAtLeast', [new Arg(new Variable('configurationId')), new Arg(new Variable('value'))]))],
            ]);

            return $node;
        }
        if (!$this->isObjectType($node, new ObjectType('Shopware\Storefront\Framework\Routing\AbstractDomainLoader')) || $node->getMethod('loadDomains') !== null) {
            return null;
        }
        $load = $node->getMethod('load');
        if ($load === null || $load->params !== [] || count($load->stmts ?? []) !== 1 || !$load->stmts[0] instanceof Return_) {
            return null;
        }
        $call = $load->stmts[0]->expr;
        if (!$call instanceof MethodCall || $call->args !== [] || !$this->isName($call->name, 'load') || !$this->isObjectType($call->var, new ObjectType('Shopware\Storefront\Framework\Routing\AbstractDomainLoader'))) {
            return null;
        }
        $load->name = new Identifier('loadDomains');
        $load->returnType = new FullyQualified('Shopware\Storefront\Framework\Routing\Struct\DomainCollection');
        $call->name = new Identifier('loadDomains');

        return $node;
    }
}
