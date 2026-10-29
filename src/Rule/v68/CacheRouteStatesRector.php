<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use Rector\NodeAnalyzer\ExprAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class CacheRouteStatesRector extends AbstractRector
{
    public function __construct(private readonly ExprAnalyzer $exprAnalyzer) {}

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove obsolete HTTP cache states from route defaults', [new CodeSample(
            "[PlatformRequest::ATTRIBUTE_HTTP_CACHE => ['states' => ['cart-filled'], 'maxAge' => 60]];",
            "[PlatformRequest::ATTRIBUTE_HTTP_CACHE => ['maxAge' => 60]];",
        )]);
    }

    public function getNodeTypes(): array
    {
        return [Array_::class];
    }

    /** @param Array_ $node */
    public function refactor(Node $node): ?Node
    {
        $changed = false;
        foreach ($node->items as $item) {
            if ($item === null || !$item->key instanceof ClassConstFetch || !$item->value instanceof Array_) {
                continue;
            }
            if (!$this->isName($item->key->class, 'Shopware\Core\PlatformRequest') || !$this->isName($item->key->name, 'ATTRIBUTE_HTTP_CACHE')) {
                continue;
            }
            foreach ($item->value->items as $key => $option) {
                if ($option !== null && $option->key instanceof Node\Scalar\String_ && $option->key->value === 'states' && !$this->exprAnalyzer->isDynamicExpr($option->value)) {
                    unset($item->value->items[$key]);
                    $changed = true;
                }
            }
        }

        return $changed ? $node : null;
    }
}
