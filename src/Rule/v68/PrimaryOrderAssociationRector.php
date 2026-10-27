<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class PrimaryOrderAssociationRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Read the primary order delivery and transaction from the order', [new CodeSample(
            '$order->getDeliveries()?->first();',
            '$order->getPrimaryOrderDelivery();',
        )]);
    }

    public function getNodeTypes(): array
    {
        return [MethodCall::class, NullsafeMethodCall::class];
    }

    /** @param MethodCall|NullsafeMethodCall $node */
    public function refactor(Node $node): ?Node
    {
        $collection = $node->var;
        if ((!$collection instanceof MethodCall && !$collection instanceof NullsafeMethodCall) || $node->args !== [] || $collection->args !== []) {
            return null;
        }
        if (!$this->isObjectType($collection->var, new ObjectType('Shopware\Core\Checkout\Order\OrderEntity'))) {
            return null;
        }
        $method = match (true) {
            $this->isName($collection->name, 'getDeliveries') && $this->isName($node->name, 'first') => 'getPrimaryOrderDelivery',
            $this->isName($collection->name, 'getTransactions') && $this->isName($node->name, 'last') => 'getPrimaryOrderTransaction',
            default => null,
        };
        if ($method === null) {
            return null;
        }

        return $collection instanceof NullsafeMethodCall ? new NullsafeMethodCall($collection->var, $method) : new MethodCall($collection->var, $method);
    }
}
