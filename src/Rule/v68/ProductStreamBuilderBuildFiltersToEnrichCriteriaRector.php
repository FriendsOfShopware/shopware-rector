<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ProductStreamBuilderBuildFiltersToEnrichCriteriaRector extends AbstractRector
{
    private const INTERFACE = 'Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replace ProductStreamBuilderInterface::buildFilters() with AbstractProductStreamBuilder::enrichCriteria()',
            [
                new CodeSample(
                    <<<'PHP'
                        $filters = $this->productStreamBuilder->buildFilters($streamId, $context);
                        $criteria->addFilter(...$filters);
                        PHP,
                    <<<'PHP'
                        $this->productStreamBuilder->enrichCriteria($criteria, $streamId, $context);
                        PHP,
                ),
            ],
        );
    }

    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class];
    }

    /**
     * @param ClassMethod|Function_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }

        [$newStmts, $changed] = $this->processStmts($node->stmts);

        if (!$changed) {
            return null;
        }

        $node->stmts = $newStmts;

        return $node;
    }

    /**
     * @param Node\Stmt[] $stmts
     *
     * @return array{Node\Stmt[], bool}
     */
    private function processStmts(array $stmts): array
    {
        $newStmts = [];
        $changed = false;
        $count = count($stmts);

        for ($i = 0; $i < $count; $i++) {
            $stmt = $stmts[$i];
            $nextStmt = $stmts[$i + 1] ?? null;

            // Pattern 1: $var = $x->buildFilters($id, $ctx); $criteria->addFilter(...$var);
            $enrichCriteriaCall = $this->matchAssignPlusAddFilter($stmt, $nextStmt);
            if ($enrichCriteriaCall !== null) {
                $newStmts[] = new Expression($enrichCriteriaCall);
                $i++;
                $changed = true;
                continue;
            }

            // Pattern 2: $criteria->addFilter(...$x->buildFilters($id, $ctx));
            $enrichCriteriaCall = $this->matchInlineAddFilter($stmt);
            if ($enrichCriteriaCall !== null) {
                $newStmts[] = new Expression($enrichCriteriaCall);
                $changed = true;
                continue;
            }

            // Fallback: buildFilters() in an unrecognised context — add a TODO comment
            if ($this->findBuildFiltersCall($stmt) !== null) {
                $stmt->setAttribute('comments', [
                    ...$stmt->getComments(),
                    new Comment('// TODO: Replace buildFilters() call with AbstractProductStreamBuilder::enrichCriteria() - please check manually'),
                ]);
                $newStmts[] = $stmt;
                $changed = true;
                continue;
            }

            $newStmts[] = $stmt;
        }

        return [$newStmts, $changed];
    }

    private function matchAssignPlusAddFilter(Node\Stmt $stmt, ?Node\Stmt $nextStmt): ?MethodCall
    {
        if (!$stmt instanceof Expression || !$stmt->expr instanceof Assign) {
            return null;
        }

        $assign = $stmt->expr;
        if (!$assign->var instanceof Variable || !$assign->expr instanceof MethodCall) {
            return null;
        }

        $buildFiltersCall = $assign->expr;
        if (!$this->isName($buildFiltersCall->name, 'buildFilters')) {
            return null;
        }
        if (!$this->isObjectType($buildFiltersCall->var, new ObjectType(self::INTERFACE))) {
            return null;
        }
        if (count($buildFiltersCall->args) !== 2) {
            return null;
        }

        $filtersVarName = $assign->var->name;

        if (!$nextStmt instanceof Expression || !$nextStmt->expr instanceof MethodCall) {
            return null;
        }

        $addFilterCall = $nextStmt->expr;
        if (!$this->isName($addFilterCall->name, 'addFilter')) {
            return null;
        }

        foreach ($addFilterCall->args as $arg) {
            if ($arg instanceof Arg && $arg->unpack && $arg->value instanceof Variable && $arg->value->name === $filtersVarName) {
                return new MethodCall(
                    $buildFiltersCall->var,
                    'enrichCriteria',
                    [
                        new Arg($addFilterCall->var),
                        $buildFiltersCall->args[0],
                        $buildFiltersCall->args[1],
                    ],
                );
            }
        }

        return null;
    }

    private function matchInlineAddFilter(Node\Stmt $stmt): ?MethodCall
    {
        if (!$stmt instanceof Expression || !$stmt->expr instanceof MethodCall) {
            return null;
        }

        $addFilterCall = $stmt->expr;
        if (!$this->isName($addFilterCall->name, 'addFilter')) {
            return null;
        }

        foreach ($addFilterCall->args as $arg) {
            if (!$arg instanceof Arg || !$arg->unpack || !$arg->value instanceof MethodCall) {
                continue;
            }

            $buildFiltersCall = $arg->value;
            if (!$this->isName($buildFiltersCall->name, 'buildFilters')) {
                continue;
            }
            if (!$this->isObjectType($buildFiltersCall->var, new ObjectType(self::INTERFACE))) {
                continue;
            }
            if (count($buildFiltersCall->args) !== 2) {
                continue;
            }

            return new MethodCall(
                $buildFiltersCall->var,
                'enrichCriteria',
                [
                    new Arg($addFilterCall->var),
                    $buildFiltersCall->args[0],
                    $buildFiltersCall->args[1],
                ],
            );
        }

        return null;
    }

    private function findBuildFiltersCall(Node $node): ?MethodCall
    {
        if ($node instanceof MethodCall
            && $this->isName($node->name, 'buildFilters')
            && $this->isObjectType($node->var, new ObjectType(self::INTERFACE))
        ) {
            return $node;
        }

        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNode = $node->{$subNodeName};
            if ($subNode instanceof Node) {
                $result = $this->findBuildFiltersCall($subNode);
                if ($result !== null) {
                    return $result;
                }
            } elseif (is_array($subNode)) {
                foreach ($subNode as $item) {
                    if ($item instanceof Node) {
                        $result = $this->findBuildFiltersCall($item);
                        if ($result !== null) {
                            return $result;
                        }
                    }
                }
            }
        }

        return null;
    }
}
