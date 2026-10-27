<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RemoteThumbnailMediaRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Write remote thumbnail metadata to the media entity', [new CodeSample(
            '$extension->mediaPath = $path;',
            "\$extension->mediaEntity->assign(['path' => \$path]);",
        )]);
    }

    public function getNodeTypes(): array
    {
        return [Expression::class];
    }

    /** @param Expression $node */
    public function refactor(Node $node): ?Node
    {
        if (!$node->expr instanceof Assign || !$node->expr->var instanceof PropertyFetch) {
            return null;
        }
        $fetch = $node->expr->var;
        $property = match ($this->getName($fetch->name)) {
            'mediaPath' => 'path',
            'mediaUpdatedAt' => 'updatedAt',
            default => null,
        };
        if ($property === null || !$this->isObjectType($fetch->var, new ObjectType('Shopware\Core\Content\Media\Extension\ResolveRemoteThumbnailUrlExtension'))) {
            return null;
        }
        $node->expr = new MethodCall(new PropertyFetch($fetch->var, 'mediaEntity'), 'assign', [new Arg(new Array_([new ArrayItem($node->expr->expr, new String_($property))]))]);

        return $node;
    }
}
