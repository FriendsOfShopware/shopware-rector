<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PHPStan\Type\ObjectType;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ProductStreamBuilderBuildFiltersToEnrichCriteriaRector extends AbstractRector
{
    private const INTERFACE = 'Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface';

    private const ABSTRACT_CLASS = 'Shopware\Core\Content\ProductStream\Service\AbstractProductStreamBuilder';

    private const CRITERIA = 'Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria';

    private const TEMPORARY_CRITERIA_VARIABLE = 'productStreamFilterCriteria';

    private const LEGACY_BRANCH_ATTRIBUTE = 'frosh_product_stream_builder_legacy_branch';

    private const MANUAL_MIGRATION_COMMENT = '// TODO: Replace buildFilters() call with AbstractProductStreamBuilder::enrichCriteria() - please check manually';

    /**
     * Expressions that do not always evaluate all of their operands.
     * Moving a buildFilters() call out of them would change behaviour.
     *
     * @var list<class-string<Expr>>
     */
    private const CONDITIONAL_EXPRESSIONS = [
        Expr\BinaryOp\BooleanAnd::class,
        Expr\BinaryOp\BooleanOr::class,
        Expr\BinaryOp\LogicalAnd::class,
        Expr\BinaryOp\LogicalOr::class,
        Expr\BinaryOp\Coalesce::class,
        Expr\AssignOp\Coalesce::class,
        Expr\Ternary::class,
        Expr\Match_::class,
        Expr\Closure::class,
        Expr\ArrowFunction::class,
        Expr\NullsafeMethodCall::class,
        Expr\NullsafePropertyFetch::class,
    ];

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
                new CodeSample(
                    <<<'PHP'
                        $filters = $this->productStreamBuilder->buildFilters($streamId, $context);
                        PHP,
                    <<<'PHP'
                        $productStreamFilterCriteria = new \Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria();
                        $this->productStreamBuilder->enrichCriteria($productStreamFilterCriteria, $streamId, $context);
                        $filters = array_values($productStreamFilterCriteria->getFilters());
                        PHP,
                ),
            ],
        );
    }

    public function getNodeTypes(): array
    {
        return NodeGroup::STMTS_AWARE;
    }

    public function refactor(Node $node): ?Node
    {
        if ($node instanceof If_) {
            $this->markLegacyBranches($node);
        }

        if ($node->getAttribute(self::LEGACY_BRANCH_ATTRIBUTE) === true) {
            return null;
        }

        if (!property_exists($node, 'stmts') || !\is_array($node->stmts)) {
            return null;
        }

        $newStmts = $this->processStmts($node->stmts);
        if ($newStmts === null) {
            return null;
        }

        $node->stmts = $newStmts;

        return $node;
    }

    /**
     * Dual-path code that checks the builder type with instanceof keeps its legacy branch untouched.
     * That branch is dead code once the interface is gone and has to be removed manually.
     */
    private function markLegacyBranches(If_ $if): void
    {
        $thenIsLegacy = $this->isLegacyThenBranch($if->cond);
        if ($thenIsLegacy === true) {
            $if->setAttribute(self::LEGACY_BRANCH_ATTRIBUTE, true);
        } elseif ($thenIsLegacy === false && $if->else !== null) {
            $if->else->setAttribute(self::LEGACY_BRANCH_ATTRIBUTE, true);
        }

        foreach ($if->elseifs as $elseIf) {
            if ($this->isLegacyThenBranch($elseIf->cond) === true) {
                $elseIf->setAttribute(self::LEGACY_BRANCH_ATTRIBUTE, true);
            }
        }
    }

    /**
     * @return bool|null true: the "then" branch is the legacy one, false: the "else" branch is, null: no builder type check
     */
    private function isLegacyThenBranch(Expr $cond): ?bool
    {
        $negated = false;
        if ($cond instanceof BooleanNot) {
            $negated = true;
            $cond = $cond->expr;
        }

        if (!$cond instanceof Instanceof_ || !$cond->class instanceof Name) {
            return null;
        }

        if ($this->isName($cond->class, self::INTERFACE)) {
            return !$negated;
        }

        if ($this->isName($cond->class, self::ABSTRACT_CLASS)) {
            return $negated;
        }

        return null;
    }

    /**
     * @param array<Stmt> $stmts
     *
     * @return array<Stmt>|null
     */
    private function processStmts(array $stmts): ?array
    {
        $stmts = array_values($stmts);
        $newStmts = [];
        $changed = false;

        foreach ($stmts as $index => $stmt) {
            // $filters = $builder->buildFilters($id, $context); ... $criteria->addFilter(...$filters);
            $replacement = $this->matchAssignWithLaterAddFilter($stmts, $index);
            if ($replacement !== null) {
                [$addFilterIndex, $enrichCriteriaCall] = $replacement;
                $stmts[$addFilterIndex] = new Expression($enrichCriteriaCall);
                $changed = true;

                continue;
            }

            // $criteria->addFilter(...$builder->buildFilters($id, $context));
            $enrichCriteriaCall = $this->matchInlineAddFilter($stmts[$index]);
            if ($enrichCriteriaCall !== null) {
                $newStmts[] = new Expression($enrichCriteriaCall);
                $changed = true;

                continue;
            }

            // Any other usage: build the filters through a temporary criteria, like AbstractProductStreamBuilder::buildFilters() does
            $hoisted = $this->hoistThroughTemporaryCriteria($stmts[$index]);
            if ($hoisted !== null) {
                array_push($newStmts, ...$hoisted);
                $changed = true;

                continue;
            }

            $stmt = $stmts[$index];
            if ($this->findBuildFiltersCall($stmt) !== null && !$this->hasManualMigrationComment($stmt)) {
                $stmt->setAttribute('comments', [
                    ...$stmt->getComments(),
                    new Comment(self::MANUAL_MIGRATION_COMMENT),
                ]);
                $changed = true;
            }

            $newStmts[] = $stmt;
        }

        return $changed ? $newStmts : null;
    }

    /**
     * @param array<int, Stmt> $stmts
     *
     * @return array{int, MethodCall}|null
     */
    private function matchAssignWithLaterAddFilter(array $stmts, int $index): ?array
    {
        $stmt = $stmts[$index];
        if (!$stmt instanceof Expression || !$stmt->expr instanceof Assign) {
            return null;
        }

        $assign = $stmt->expr;
        if (!$assign->var instanceof Variable || !\is_string($assign->var->name)) {
            return null;
        }

        $buildFiltersCall = $assign->expr;
        if (!$buildFiltersCall instanceof MethodCall || !$this->isBuildFiltersCall($buildFiltersCall)) {
            return null;
        }

        $variableName = $assign->var->name;
        $nodeFinder = new NodeFinder();
        $match = null;

        // The variable must be used exactly once afterwards, and that use must be addFilter(...$variable)
        for ($i = $index + 1, $count = \count($stmts); $i < $count; $i++) {
            $usages = $nodeFinder->find(
                $stmts[$i],
                static fn (Node $node): bool => $node instanceof Variable && $node->name === $variableName,
            );

            if ($usages === []) {
                continue;
            }

            if ($match !== null || \count($usages) !== 1) {
                return null;
            }

            $addFilterArgument = $this->matchSingleUnpackedAddFilterArgument($stmts[$i]);
            if ($addFilterArgument === null
                || !$addFilterArgument[1] instanceof Variable
                || $addFilterArgument[1]->name !== $variableName
            ) {
                return null;
            }

            $match = [$i, $this->createEnrichCriteriaCall($buildFiltersCall, $addFilterArgument[0])];
        }

        return $match;
    }

    private function matchInlineAddFilter(Stmt $stmt): ?MethodCall
    {
        $addFilterArgument = $this->matchSingleUnpackedAddFilterArgument($stmt);
        if ($addFilterArgument === null) {
            return null;
        }

        [$criteria, $value] = $addFilterArgument;
        if (!$value instanceof MethodCall || !$this->isBuildFiltersCall($value)) {
            return null;
        }

        return $this->createEnrichCriteriaCall($value, $criteria);
    }

    /**
     * Matches `$criteria->addFilter(...$value)` with exactly one argument.
     *
     * @return array{Expr, Expr}|null the criteria and the unpacked value
     */
    private function matchSingleUnpackedAddFilterArgument(Stmt $stmt): ?array
    {
        if (!$stmt instanceof Expression || !$stmt->expr instanceof MethodCall) {
            return null;
        }

        $addFilterCall = $stmt->expr;
        if (!$this->isName($addFilterCall->name, 'addFilter') || \count($addFilterCall->args) !== 1) {
            return null;
        }

        $arg = $addFilterCall->args[0];
        if (!$arg instanceof Arg || !$arg->unpack) {
            return null;
        }

        return [$addFilterCall->var, $arg->value];
    }

    /**
     * @return array<Stmt>|null
     */
    private function hoistThroughTemporaryCriteria(Stmt $stmt): ?array
    {
        if (!$stmt instanceof Expression && !$stmt instanceof Return_ && !$stmt instanceof Foreach_
            && !$stmt instanceof If_ && !$stmt instanceof Echo_) {
            return null;
        }

        $buildFiltersCall = $this->findBuildFiltersCall($stmt);
        if ($buildFiltersCall === null || !$this->isBuildFiltersCall($buildFiltersCall)) {
            return null;
        }

        if ($this->isConditionallyEvaluated($stmt, $buildFiltersCall)) {
            return null;
        }

        $temporaryCriteria = new Variable(self::TEMPORARY_CRITERIA_VARIABLE);

        $this->traverseNodesWithCallable(
            $stmt,
            static fn (Node $node): ?Node => $node === $buildFiltersCall
                ? new FuncCall(new Name('array_values'), [new Arg(new MethodCall($temporaryCriteria, 'getFilters'))])
                : null,
        );

        return [
            new Expression(new Assign($temporaryCriteria, new New_(new FullyQualified(self::CRITERIA)))),
            new Expression($this->createEnrichCriteriaCall($buildFiltersCall, $temporaryCriteria)),
            $stmt,
        ];
    }

    private function isConditionallyEvaluated(Stmt $stmt, MethodCall $buildFiltersCall): bool
    {
        $nodeFinder = new NodeFinder();
        $conditionalExpressions = $nodeFinder->find(
            $stmt,
            static function (Node $node): bool {
                foreach (self::CONDITIONAL_EXPRESSIONS as $class) {
                    if ($node instanceof $class) {
                        return true;
                    }
                }

                return false;
            },
        );

        foreach ($conditionalExpressions as $conditionalExpression) {
            if ($nodeFinder->findFirst($conditionalExpression, static fn (Node $node): bool => $node === $buildFiltersCall) !== null) {
                return true;
            }
        }

        return false;
    }

    private function createEnrichCriteriaCall(MethodCall $buildFiltersCall, Expr $criteria): MethodCall
    {
        return new MethodCall(
            $buildFiltersCall->var,
            'enrichCriteria',
            [new Arg($criteria), $buildFiltersCall->args[0], $buildFiltersCall->args[1]],
        );
    }

    private function isBuildFiltersCall(MethodCall $methodCall): bool
    {
        if (!$this->isName($methodCall->name, 'buildFilters') || \count($methodCall->args) !== 2) {
            return false;
        }

        foreach ($methodCall->args as $arg) {
            if (!$arg instanceof Arg || $arg->unpack || $arg->name !== null) {
                return false;
            }
        }

        return $this->isObjectType($methodCall->var, new ObjectType(self::INTERFACE));
    }

    /**
     * Finds a buildFilters() call in the statement itself, without descending into nested statements.
     * Nested statement lists are visited on their own, see getNodeTypes().
     */
    private function findBuildFiltersCall(Node $node, bool $isRoot = true): ?MethodCall
    {
        if (!$isRoot && $node instanceof Stmt) {
            return null;
        }

        if ($node instanceof MethodCall
            && $this->isName($node->name, 'buildFilters')
            && $this->isObjectType($node->var, new ObjectType(self::INTERFACE))
        ) {
            return $node;
        }

        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNodes = $node->{$subNodeName};
            foreach (\is_array($subNodes) ? $subNodes : [$subNodes] as $subNode) {
                if (!$subNode instanceof Node) {
                    continue;
                }

                $result = $this->findBuildFiltersCall($subNode, false);
                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }

    private function hasManualMigrationComment(Stmt $stmt): bool
    {
        foreach ($stmt->getComments() as $comment) {
            if ($comment->getText() === self::MANUAL_MIGRATION_COMMENT) {
                return true;
            }
        }

        return false;
    }
}
