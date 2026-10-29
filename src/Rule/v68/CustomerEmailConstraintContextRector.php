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

final class CustomerEmailConstraintContextRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Read the email uniqueness constraint context through its sales channel context', [new CodeSample(
            '$constraint->getContext();',
            '$constraint->getSalesChannelContext()->getContext();',
        )]);
    }

    public function getNodeTypes(): array
    {
        return [MethodCall::class, NullsafeMethodCall::class];
    }

    /** @param MethodCall|NullsafeMethodCall $node */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node->name, 'getContext') || $node->args !== [] || !$this->isObjectType($node->var, new ObjectType('Shopware\Core\Checkout\Customer\Validation\Constraint\CustomerEmailUnique'))) {
            return null;
        }
        $context = $node instanceof NullsafeMethodCall ? new NullsafeMethodCall($node->var, 'getSalesChannelContext') : new MethodCall($node->var, 'getSalesChannelContext');

        return new MethodCall($context, 'getContext');
    }
}
