<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PHPStan\Type\ObjectType;
use Rector\PhpParser\Node\FileNode;
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
        Closure::class,
        Expr\ArrowFunction::class,
        Expr\NullsafeMethodCall::class,
        Expr\NullsafePropertyFetch::class,
    ];

    /**
     * Expressions that can change state, so a buildFilters() call must not be moved across them.
     *
     * @var list<class-string<Expr>>
     */
    private const SIDE_EFFECT_EXPRESSIONS = [
        Assign::class,
        Expr\AssignOp::class,
        Expr\AssignRef::class,
        Expr\PreInc::class,
        Expr\PreDec::class,
        Expr\PostInc::class,
        Expr\PostDec::class,
        FuncCall::class,
        MethodCall::class,
        Expr\NullsafeMethodCall::class,
        Expr\StaticCall::class,
        New_::class,
        Expr\Include_::class,
        Expr\Eval_::class,
        Expr\Exit_::class,
        Expr\Print_::class,
        Expr\Yield_::class,
        Expr\YieldFrom::class,
        Expr\Throw_::class,
        Expr\ShellExec::class,
    ];

    /**
     * Functions that read or write variables without naming them.
     */
    private const DYNAMIC_VARIABLE_ACCESS_FUNCTIONS = ['compact', 'extract', 'get_defined_vars', 'parse_str'];

    /**
     * Variable names in use in the scope that is currently processed, including the temporary criteria allocated so far.
     *
     * @var array<string, true>
     */
    private array $usedVariableNames = [];

    private ?Node $scope = null;

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

    /**
     * Every node type that opens a PHP variable scope. Nested statement lists (if, loops, try, ...) are walked
     * from there, so checks on variables always see the whole scope.
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class, Closure::class, Namespace_::class, FileNode::class];
    }

    /**
     * @param ClassMethod|Function_|Closure|Namespace_|FileNode $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->getAttribute(self::LEGACY_BRANCH_ATTRIBUTE) === true || $node->stmts === null) {
            return null;
        }

        $this->scope = $node;
        $this->usedVariableNames = [];
        foreach ((new NodeFinder())->findInstanceOf($node, Variable::class) as $variable) {
            if (\is_string($variable->name)) {
                $this->usedVariableNames[$variable->name] = true;
            }
        }

        $newStmts = $this->processStmts($node->stmts);
        $this->scope = null;

        if ($newStmts === null) {
            return null;
        }

        $node->stmts = $newStmts;

        return $node;
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
            if ($this->findBuildFiltersCalls($stmt) !== [] && !$this->hasManualMigrationComment($stmt)) {
                $stmt->setAttribute('comments', [
                    ...$stmt->getComments(),
                    new Comment(self::MANUAL_MIGRATION_COMMENT),
                ]);
                $changed = true;
            }

            $newStmts[] = $stmt;
        }

        foreach ($newStmts as $stmt) {
            if ($this->processNestedStmts($stmt)) {
                $changed = true;
            }
        }

        return $changed ? $newStmts : null;
    }

    /**
     * Processes the statement lists nested in a statement (if, else, loops, try, catch, ...) within the same variable scope.
     * Classes, functions and closures open a scope of their own and are visited on their own, see getNodeTypes().
     */
    private function processNestedStmts(Stmt $stmt): bool
    {
        if ($stmt instanceof ClassLike || $stmt instanceof FunctionLike || $stmt instanceof Namespace_) {
            return false;
        }

        if ($stmt instanceof If_) {
            $this->markLegacyBranches($stmt);
        }

        $changed = false;
        foreach ($stmt->getSubNodeNames() as $subNodeName) {
            $subNode = $stmt->{$subNodeName};

            if ($subNodeName === 'stmts' && \is_array($subNode)) {
                if ($stmt->getAttribute(self::LEGACY_BRANCH_ATTRIBUTE) === true) {
                    $this->markClosuresAsLegacy($subNode);

                    continue;
                }

                $newStmts = $this->processStmts($subNode);
                if ($newStmts !== null) {
                    $stmt->{$subNodeName} = $newStmts;
                    $changed = true;
                }

                continue;
            }

            foreach (\is_array($subNode) ? $subNode : [$subNode] as $nestedStmt) {
                if ($nestedStmt instanceof Stmt && $this->processNestedStmts($nestedStmt)) {
                    $changed = true;
                }
            }
        }

        return $changed;
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
     * Closures are visited on their own, so they need to know that they are part of a legacy branch.
     *
     * @param array<Stmt> $stmts
     */
    private function markClosuresAsLegacy(array $stmts): void
    {
        foreach ((new NodeFinder())->findInstanceOf($stmts, Closure::class) as $closure) {
            $closure->setAttribute(self::LEGACY_BRANCH_ATTRIBUTE, true);
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

        // In the whole scope, the variable may only be written here and read in the addFilter() call,
        // otherwise removing the assignment changes what other readers see
        $variableName = $assign->var->name;
        if ($this->scope === null
            || $this->hasDynamicVariableAccess($this->scope)
            || $this->countVariableUsages($this->scope, $variableName) !== 2
        ) {
            return null;
        }

        $nodeFinder = new NodeFinder();
        for ($i = $index + 1, $count = \count($stmts); $i < $count; $i++) {
            $usage = $nodeFinder->findFirst(
                $stmts[$i],
                static fn (Node $node): bool => $node instanceof Variable && $node->name === $variableName,
            );

            if ($usage === null) {
                continue;
            }

            $addFilterArgument = $this->matchSingleUnpackedAddFilterArgument($stmts[$i]);
            if ($addFilterArgument === null
                || !$addFilterArgument[1] instanceof Variable
                || $addFilterArgument[1]->name !== $variableName
                || !$this->canMoveAcross(\array_slice($stmts, $index + 1, $i - $index - 1), $buildFiltersCall)
            ) {
                return null;
            }

            return [$i, $this->createEnrichCriteriaCall($buildFiltersCall, $addFilterArgument[0])];
        }

        return null;
    }

    /**
     * Whether the buildFilters() call can be executed after the given statements, at the position of the addFilter() call,
     * without changing its result. The statements must not touch anything the call reads and must not leave early.
     *
     * @param array<Stmt> $stmts
     */
    private function canMoveAcross(array $stmts, MethodCall $buildFiltersCall): bool
    {
        if ($stmts === []) {
            return true;
        }

        $variableNames = [];
        $propertyNames = [];
        $readsThis = false;
        foreach ([$buildFiltersCall->var, $buildFiltersCall->args[0], $buildFiltersCall->args[1]] as $dependency) {
            foreach ([$dependency, ...(new NodeFinder())->find($dependency, static fn (): bool => true)] as $node) {
                if ($node instanceof Variable) {
                    if (!\is_string($node->name)) {
                        return false;
                    }

                    if ($node->name === 'this') {
                        $readsThis = true;
                    } else {
                        $variableNames[$node->name] = true;
                    }
                } elseif ($node instanceof PropertyFetch) {
                    if (!$node->name instanceof Identifier) {
                        return false;
                    }

                    $propertyNames[$node->name->toString()] = true;
                } elseif (!$node instanceof Arg && !$node instanceof Identifier && !$node instanceof Name
                    && !$node instanceof Expr\ConstFetch && !$node instanceof Expr\ClassConstFetch && !$node instanceof Scalar
                ) {
                    // Anything else, like a method call in an argument, could return something else later
                    return false;
                }
            }
        }

        $conflict = (new NodeFinder())->findFirst($stmts, static function (Node $node) use ($variableNames, $propertyNames, $readsThis): bool {
            if ($node instanceof Variable) {
                return !\is_string($node->name) || isset($variableNames[$node->name]);
            }

            if ($node instanceof PropertyFetch || $node instanceof Expr\NullsafePropertyFetch || $node instanceof Expr\StaticPropertyFetch) {
                return !$node->name instanceof Identifier || isset($propertyNames[$node->name->toString()]);
            }

            // A method of the own class could change the properties the call reads
            if ($readsThis && ($node instanceof MethodCall || $node instanceof Expr\NullsafeMethodCall)) {
                return $node->var instanceof Variable && $node->var->name === 'this';
            }

            if ($readsThis && $node instanceof Expr\StaticCall) {
                return $node->class instanceof Name && \in_array($node->class->toLowerString(), ['self', 'static', 'parent'], true);
            }

            if ($node instanceof FuncCall) {
                return $node->name instanceof Name && \in_array($node->name->toLowerString(), self::DYNAMIC_VARIABLE_ACCESS_FUNCTIONS, true);
            }

            return $node instanceof Return_
                || $node instanceof Stmt\Break_
                || $node instanceof Stmt\Continue_
                || $node instanceof Stmt\Goto_
                || $node instanceof Stmt\Label
                || $node instanceof Expr\Throw_
                || $node instanceof Expr\Exit_
                || $node instanceof Expr\Yield_
                || $node instanceof Expr\YieldFrom
                || $node instanceof Expr\Include_
                || $node instanceof Expr\Eval_;
        });

        return $conflict === null;
    }

    private function countVariableUsages(Node $scope, string $variableName): int
    {
        return \count((new NodeFinder())->find(
            $scope,
            static fn (Node $node): bool => $node instanceof Variable && $node->name === $variableName,
        ));
    }

    private function hasDynamicVariableAccess(Node $scope): bool
    {
        return (new NodeFinder())->findFirst($scope, static function (Node $node): bool {
            if ($node instanceof Variable) {
                return !\is_string($node->name);
            }

            return $node instanceof FuncCall
                && $node->name instanceof Name
                && \in_array($node->name->toLowerString(), self::DYNAMIC_VARIABLE_ACCESS_FUNCTIONS, true);
        }) !== null;
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

        $buildFiltersCalls = $this->findBuildFiltersCalls($stmt);
        if ($buildFiltersCalls === []) {
            return null;
        }

        foreach ($buildFiltersCalls as $buildFiltersCall) {
            if (!$this->isBuildFiltersCall($buildFiltersCall)
                || $this->isConditionallyEvaluated($stmt, $buildFiltersCall)
                || $this->hasEarlierSideEffect($stmt, $buildFiltersCall, $buildFiltersCalls)
            ) {
                return null;
            }
        }

        // Each call gets its own temporary criteria, so no call overwrites a variable that is still read
        $hoisted = [];
        $replacements = new \SplObjectStorage();
        foreach ($buildFiltersCalls as $buildFiltersCall) {
            $temporaryCriteria = new Variable($this->allocateTemporaryVariableName());
            $hoisted[] = new Expression(new Assign($temporaryCriteria, new New_(new FullyQualified(self::CRITERIA))));
            $hoisted[] = new Expression($this->createEnrichCriteriaCall($buildFiltersCall, $temporaryCriteria));
            $replacements[$buildFiltersCall] = new FuncCall(
                new Name('array_values'),
                [new Arg(new MethodCall($temporaryCriteria, 'getFilters'))],
            );
        }

        $this->traverseNodesWithCallable(
            $stmt,
            static fn (Node $node): ?Node => $node instanceof MethodCall && isset($replacements[$node])
                ? $replacements[$node]
                : null,
        );

        return [...$hoisted, $stmt];
    }

    /**
     * Whether something that runs before the buildFilters() call in the statement can change state.
     * Hoisting the call in front of the statement would then change what it reads.
     * Other buildFilters() calls are hoisted in the same order, so they keep their relative order.
     *
     * @param list<MethodCall> $buildFiltersCalls
     */
    private function hasEarlierSideEffect(Stmt $stmt, MethodCall $buildFiltersCall, array $buildFiltersCalls): bool
    {
        $callStart = $buildFiltersCall->getStartFilePos();
        if ($callStart < 0) {
            return true;
        }

        $nodeFinder = new NodeFinder();
        foreach ($this->findSideEffects($stmt, $buildFiltersCalls, true) as $sideEffect) {
            $isAncestor = $nodeFinder->findFirst($sideEffect, static fn (Node $node): bool => $node === $buildFiltersCall) !== null;
            if ($isAncestor) {
                continue;
            }

            $start = $sideEffect->getStartFilePos();
            if ($start < 0 || $start < $callStart) {
                return true;
            }
        }

        return false;
    }

    /**
     * Collects side effect expressions of the statement itself, without nested statements, function bodies and
     * the buildFilters() calls that are hoisted.
     *
     * @param list<MethodCall> $buildFiltersCalls
     *
     * @return list<Expr>
     */
    private function findSideEffects(Node $node, array $buildFiltersCalls, bool $isRoot = false): array
    {
        if ((!$isRoot && $node instanceof Stmt)
            || $node instanceof Closure
            || $node instanceof Expr\ArrowFunction
            || \in_array($node, $buildFiltersCalls, true)
        ) {
            return [];
        }

        $sideEffects = [];
        foreach (self::SIDE_EFFECT_EXPRESSIONS as $class) {
            if ($node instanceof $class) {
                $sideEffects[] = $node;

                break;
            }
        }

        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNodes = $node->{$subNodeName};
            foreach (\is_array($subNodes) ? $subNodes : [$subNodes] as $subNode) {
                if ($subNode instanceof Node) {
                    array_push($sideEffects, ...$this->findSideEffects($subNode, $buildFiltersCalls));
                }
            }
        }

        return $sideEffects;
    }

    private function allocateTemporaryVariableName(): string
    {
        $name = self::TEMPORARY_CRITERIA_VARIABLE;
        for ($suffix = 2; isset($this->usedVariableNames[$name]); $suffix++) {
            $name = self::TEMPORARY_CRITERIA_VARIABLE . $suffix;
        }

        $this->usedVariableNames[$name] = true;

        return $name;
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
     * Finds the buildFilters() calls in the statement itself, without descending into nested statements.
     * Nested statement lists are processed on their own, see processNestedStmts().
     *
     * @return list<MethodCall>
     */
    private function findBuildFiltersCalls(Node $node, bool $isRoot = true): array
    {
        if (!$isRoot && $node instanceof Stmt) {
            return [];
        }

        if ($node instanceof MethodCall
            && $this->isName($node->name, 'buildFilters')
            && $this->isObjectType($node->var, new ObjectType(self::INTERFACE))
        ) {
            return [$node];
        }

        $calls = [];
        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNodes = $node->{$subNodeName};
            foreach (\is_array($subNodes) ? $subNodes : [$subNodes] as $subNode) {
                if ($subNode instanceof Node) {
                    array_push($calls, ...$this->findBuildFiltersCalls($subNode, false));
                }
            }
        }

        return $calls;
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
