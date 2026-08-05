<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class CartBehaviorIsRecalculationRector extends AbstractRector
{
    private const CART_BEHAVIOR = 'Shopware\Core\Checkout\Cart\CartBehavior';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace deprecated CartBehavior::isRecalculation() calls with the cart-persistence permission check',
            [
                new CodeSample(
                    <<<'PHP'
                        $behavior->isRecalculation();
                        PHP,
                    <<<'PHP'
                        $behavior->hasPermission(CheckoutPermissions::SKIP_CART_PERSISTENCE);
                        PHP,
                ),
            ],
        );
    }

    public function getNodeTypes(): array
    {
        return [MethodCall::class, NullsafeMethodCall::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node->name instanceof Node\Identifier || $node->name->toString() !== 'isRecalculation' || $node->args !== []) {
            return null;
        }

        if (!$this->isObjectType($node->var, new ObjectType(self::CART_BEHAVIOR))) {
            return null;
        }

        $permission = new Node\Expr\ClassConstFetch(
            new FullyQualified('Shopware\Core\Checkout\CheckoutPermissions'),
            'SKIP_CART_PERSISTENCE',
        );

        return $node instanceof NullsafeMethodCall
            ? new NullsafeMethodCall($node->var, 'hasPermission', [$this->nodeFactory->createArg($permission)])
            : new MethodCall($node->var, 'hasPermission', [$this->nodeFactory->createArg($permission)]);
    }
}
