<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class EntityDefinitionParentConstructorRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove calls to the removed EntityDefinition constructor in direct subclasses', [new CodeSample(
            'class Definition extends EntityDefinition { public function __construct() { parent::__construct(); } }',
            'class Definition extends EntityDefinition { public function __construct() {} }',
        )]);
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /** @param Class_ $node */
    public function refactor(Node $node): ?Node
    {
        if ($node->extends === null || !$this->isName($node->extends, 'Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition')) {
            return null;
        }
        $constructor = $node->getMethod('__construct');
        if ($constructor === null || $constructor->stmts === null) {
            return null;
        }
        $changed = false;
        foreach ($constructor->stmts as $key => $stmt) {
            if ($stmt instanceof Expression && $stmt->expr instanceof StaticCall && $this->isName($stmt->expr->class, 'parent') && $this->isName($stmt->expr->name, '__construct') && $stmt->expr->args === []) {
                unset($constructor->stmts[$key]);
                $changed = true;
            }
        }

        return $changed ? $node : null;
    }
}
