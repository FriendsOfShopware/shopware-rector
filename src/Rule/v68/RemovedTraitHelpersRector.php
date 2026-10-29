<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\NodeTraverser;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RemovedTraitHelpersRector extends AbstractRector
{
    private const ASSERT_TRAIT = 'Shopware\Core\Framework\Test\Api\Serializer\AssertValuesTrait';
    private const PROMOTION_TRAIT = 'Shopware\Core\Checkout\Promotion\Cart\PromotionCartInformationTrait';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace removed assertion and promotion trait helpers in classes using those traits', [new CodeSample(
            'class Test { use AssertValuesTrait; public function test(): void { $this->assertValues($expected, $actual); } }',
            'class Test { public function test(): void { ArrayValues::assertValues($expected, $actual); } }',
        )]);
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /** @param Class_ $node */
    public function refactor(Node $node): ?Node
    {
        $hasAssert = false;
        $hasPromotion = false;
        foreach ($node->getTraitUses() as $use) {
            if ($use->adaptations !== []) {
                // Aliases/precedence rules need a class-wide method resolution analysis.
                return null;
            }
            foreach ($use->traits as $trait) {
                $hasAssert = $hasAssert || $this->isName($trait, self::ASSERT_TRAIT);
                $hasPromotion = $hasPromotion || $this->isName($trait, self::PROMOTION_TRAIT);
            }
        }
        if (!$hasAssert && !$hasPromotion) {
            return null;
        }

        $changed = false;
        $keepAssert = $node->getMethod('assertValues') !== null;
        $this->traverseNodesWithCallable($node->stmts, function (Node $inner) use ($node, $hasAssert, $hasPromotion, &$changed, &$keepAssert): Node|int|null {
            if ($inner instanceof Node\Stmt\ClassLike) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
            $statement = $inner instanceof Node\Stmt\Expression ? $inner : null;
            $inner = $statement->expr ?? $inner;
            if ($inner instanceof StaticCall && $this->isName($inner->name, 'assertValues')) {
                $keepAssert = true;
            }
            if (!$inner instanceof MethodCall || !$inner->var instanceof Variable || !$this->isName($inner->var, 'this')) {
                return null;
            }
            $method = $this->getName($inner->name);
            if ($method === null) {
                $keepAssert = true;
            }
            if ($method === 'assertValues' && $hasAssert && $node->getMethod('assertValues') === null) {
                if ($statement !== null) {
                    return null;
                }
                $args = NamedArguments::resolve($inner->args, ['expected', 'actual']);
                if ($args === null || count($args) !== 2) {
                    $keepAssert = true;

                    return null;
                }
                $changed = true;

                return new StaticCall(new FullyQualified('Shopware\Core\Test\Assert\ArrayValues'), 'assertValues', $inner->args);
            }
            if ($statement === null || !$hasPromotion || !in_array($method, ['addPromotionNotFoundError', 'addPromotionNotEligibleError'], true) || $node->getMethod($method) !== null) {
                return null;
            }
            $args = NamedArguments::resolve($inner->args, [$method === 'addPromotionNotFoundError' ? 'code' : 'name', 'cart']);
            if ($args === null || count($args) !== 2 || !$args['cart']->value instanceof Variable) {
                return null;
            }
            $error = $method === 'addPromotionNotFoundError' ? 'PromotionNotFoundError' : 'PromotionNotEligibleError';
            $value = $args[$method === 'addPromotionNotFoundError' ? 'code' : 'name']->value;
            if (!$value instanceof Variable && !$value instanceof Node\Scalar\String_) {
                return null;
            }
            $changed = true;
            $statement->expr = new MethodCall($args['cart']->value, 'addErrors', [new Arg(new New_(new FullyQualified('Shopware\Core\Checkout\Promotion\Cart\Error\\' . $error), [new Arg($value)]))]);

            return $statement;
        });

        if ($hasAssert && !$keepAssert) {
            foreach ($node->stmts as $key => $stmt) {
                if (!$stmt instanceof TraitUse) {
                    continue;
                }
                $stmt->traits = array_values(array_filter($stmt->traits, fn (Node\Name $trait): bool => !$this->isName($trait, self::ASSERT_TRAIT)));
                if ($stmt->traits === []) {
                    unset($node->stmts[$key]);
                }
            }
            $changed = true;
        }

        return $changed ? $node : null;
    }
}
