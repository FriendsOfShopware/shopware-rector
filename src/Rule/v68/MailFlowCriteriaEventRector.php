<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\NullsafePropertyFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class MailFlowCriteriaEventRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrate mail flow criteria event names and accessors', [new CodeSample(
            "'flow.storer.order.criteria.event';\n\$event->getCriteria();",
            "'mail-flow.data.order.criteria.event';\n\$event->criteria;",
        )]);
    }

    public function getNodeTypes(): array
    {
        return [String_::class, MethodCall::class, NullsafeMethodCall::class];
    }

    /** @param String_|MethodCall|NullsafeMethodCall $node */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof String_) {
            if (preg_match('/^flow\.storer\.[a-zA-Z0-9_]+\.criteria\.event$/', $node->value) !== 1) {
                return null;
            }
            $node->value = 'mail-flow.data.' . substr($node->value, strlen('flow.storer.'));

            return $node;
        }
        if ($node->args !== []) {
            return null;
        }
        $property = match ($this->getName($node->name)) {
            'getCriteria' => 'criteria',
            'getEntityName' => 'entityName',
            default => null,
        };
        if ($property === null) {
            return null;
        }
        foreach (['Shopware\Core\Content\Flow\Events\BeforeLoadStorableFlowDataEvent', 'Shopware\Core\Content\Shared\MailFlow\Event\MailFlowDataCriteriaEvent'] as $class) {
            if ($this->isObjectType($node->var, new ObjectType($class))) {
                return $node instanceof NullsafeMethodCall ? new NullsafePropertyFetch($node->var, $property) : new PropertyFetch($node->var, $property);
            }
        }

        return null;
    }
}
