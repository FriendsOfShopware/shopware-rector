<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\PhpParser\Node\BetterNodeFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ProductStreamBuilderBuildFiltersToEnrichCriteriaRector extends AbstractRector
{
    private const INTERFACE = 'Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface';

    public function __construct(private readonly BetterNodeFinder $betterNodeFinder) {}

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
        return [ClassMethod::class, Function_::class, Closure::class, Node\Stmt\If_::class, Node\Stmt\ElseIf_::class, Node\Stmt\Else_::class, Node\Stmt\Foreach_::class, Node\Stmt\For_::class, Node\Stmt\While_::class, Node\Stmt\Do_::class, Node\Stmt\TryCatch::class, Node\Stmt\Catch_::class, Node\Stmt\Finally_::class, Node\Stmt\Case_::class];
    }

    /**
     * @param ClassMethod|Function_|Closure|Node\Stmt\If_|Node\Stmt\ElseIf_|Node\Stmt\Else_|Node\Stmt\Foreach_|Node\Stmt\For_|Node\Stmt\While_|Node\Stmt\Do_|Node\Stmt\TryCatch|Node\Stmt\Catch_|Node\Stmt\Finally_|Node\Stmt\Case_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }

        [$newStmts, $changed] = $this->processStmts($node->stmts, $node instanceof ClassMethod || $node instanceof Function_ || $node instanceof Closure);

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
    private function processStmts(array $stmts, bool $allowAssignment): array
    {
        $newStmts = [];
        $changed = false;
        $count = count($stmts);

        for ($i = 0; $i < $count; $i++) {
            $stmt = $stmts[$i];
            $nextStmt = $stmts[$i + 1] ?? null;

            // Pattern 1: $var = $x->buildFilters($id, $ctx); $criteria->addFilter(...$var);
            $enrichCriteriaCall = $allowAssignment ? $this->matchAssignPlusAddFilter($stmt, $nextStmt) : null;
            if ($enrichCriteriaCall !== null && $stmt instanceof Expression && $stmt->expr instanceof Assign && $stmt->expr->var instanceof Variable) {
                $variable = $stmt->expr->var;
                if ($this->betterNodeFinder->findFirst(array_slice($stmts, $i + 2), static fn (Node $later): bool => $later instanceof Variable && $later->name === $variable->name) !== null) {
                    $enrichCriteriaCall = null;
                }
            }
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
            if (($stmt instanceof Expression || $stmt instanceof Node\Stmt\Return_) && $this->findBuildFiltersCall($stmt) !== null) {
                foreach ($stmt->getComments() as $comment) {
                    if (str_contains($comment->getText(), 'TODO: Replace buildFilters()')) {
                        $newStmts[] = $stmt;
                        continue 2;
                    }
                }
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
        if (!$this->isBuilder($buildFiltersCall->var)) {
            return null;
        }
        if (count($buildFiltersCall->args) !== 2) {
            return null;
        }
        foreach ($buildFiltersCall->args as $arg) {
            if (!$arg instanceof Arg || $arg->unpack || ($arg->name !== null && !in_array($arg->name->toString(), ['id', 'context'], true))) {
                return null;
            }
        }

        $filtersVarName = $assign->var->name;

        if (!$nextStmt instanceof Expression || !$nextStmt->expr instanceof MethodCall) {
            return null;
        }

        $addFilterCall = $nextStmt->expr;
        if (!$this->isName($addFilterCall->name, 'addFilter')) {
            return null;
        }
        if (count($addFilterCall->args) !== 1) {
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
        if (count($addFilterCall->args) !== 1) {
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
            if (!$this->isBuilder($buildFiltersCall->var)) {
                continue;
            }
            if (count($buildFiltersCall->args) !== 2) {
                continue;
            }
            foreach ($buildFiltersCall->args as $argument) {
                if (!$argument instanceof Arg || $argument->unpack || ($argument->name !== null && !in_array($argument->name->toString(), ['id', 'context'], true))) {
                    continue 2;
                }
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
            && $this->isBuilder($node->var)
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

    private function isBuilder(Node $node): bool
    {
        foreach ([self::INTERFACE, 'Shopware\Core\Content\ProductStream\Service\AbstractProductStreamBuilder', 'Shopware\Core\Content\ProductStream\Service\ProductStreamBuilder'] as $class) {
            if ($this->isObjectType($node, new ObjectType($class))) {
                return true;
            }
        }

        return false;
    }
}
