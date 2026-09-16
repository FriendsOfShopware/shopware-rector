<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange;

use Frosh\Rector\Version\ShopwareVersionRange;
use Frosh\Rector\Rule\BCChange\ValueObject\BCChangeRuleConfiguration;
use Frosh\Rector\Rule\BCChange\ValueObject\BCChange;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\UnaryMinus;
use PhpParser\Node\Expr\UnaryPlus;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PHPStan\Type\ObjectType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

abstract class AbstractBCChangeRector extends AbstractRector implements ConfigurableRectorInterface
{
    protected const ADD_OPTIONAL_PARAMETER = 'add_optional_parameter';
    protected const ADD_REQUIRED_PARAMETER = 'add_required_parameter';
    protected const WIDEN_PARAMETER_TYPE = 'widen_parameter_type';
    protected const NARROW_RETURN_TYPE = 'narrow_return_type';
    protected const EXPLICIT_CURRENT_DEFAULT = 'explicit_current_default';
    protected const RENAME_PARAMETER = 'rename_parameter';
    protected const REMOVE_PARAMETER = 'remove_parameter';

    /** @var list<array<string, mixed>> */
    private array $changes = [];

    private ShopwareVersionRange $versions;

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Apply Shopware BC changes for a configured supported version range.', [
            new ConfiguredCodeSample(
                'final class Extension extends CoreClass { public function load(string $id): object {} }',
                'final class Extension extends CoreClass { public function load(string $id, bool $fresh = false): Result {} }',
                [],
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [Class_::class, MethodCall::class, NullsafeMethodCall::class, StaticCall::class, New_::class];
    }

    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Class_) {
            return $this->refactorClass($node);
        }

        if (!$node instanceof MethodCall && !$node instanceof NullsafeMethodCall && !$node instanceof StaticCall && !$node instanceof New_) {
            return null;
        }

        return $this->refactorCall($node);
    }

    public function configure(array $configuration): void
    {
        $ruleConfiguration = $configuration[0] ?? null;
        if (!$ruleConfiguration instanceof BCChangeRuleConfiguration || count($configuration) !== 1) {
            throw new \LogicException(sprintf('%s expects exactly one %s instance.', static::class, BCChangeRuleConfiguration::class));
        }

        $this->versions = $ruleConfiguration->versions;
        $this->changes = [];

        foreach ($ruleConfiguration->changes as $changeConfiguration) {
            $configurationType = $this->configurationType();
            if (!$changeConfiguration instanceof $configurationType) {
                throw new \LogicException(sprintf('%s only accepts %s instances.', static::class, $configurationType));
            }

            $change = ['kind' => $this->supportedKind()] + $changeConfiguration->configuration();
            $changeVersion = ltrim($changeConfiguration->version(), 'v');
            if (!$this->versions->targetIsAtLeast($changeVersion)) {
                continue;
            }

            $this->changes[] = $change;
        }
    }

    abstract protected function supportedKind(): string;

    /** @return class-string<BCChange> */
    abstract protected function configurationType(): string;

    private function refactorClass(Class_ $class): ?Class_
    {
        $changed = false;

        foreach ($this->changes as $change) {
            if (!$this->isClassChange($change) || !$this->isObjectType($class, new ObjectType($change['class']))) {
                continue;
            }

            foreach ($class->getMethods() as $method) {
                if (!$this->isName($method->name, $change['method'])) {
                    continue;
                }

                $changed = $this->refactorMethod($method, $change) || $changed;
            }
        }

        return $changed ? $class : null;
    }

    /** @param array<string, mixed> $change */
    private function isClassChange(array $change): bool
    {
        if (in_array($change['kind'], [self::ADD_OPTIONAL_PARAMETER, self::WIDEN_PARAMETER_TYPE, self::NARROW_RETURN_TYPE], true)) {
            return true;
        }

        return $this->minimumIncludes($change)
            && in_array($change['kind'], [self::ADD_REQUIRED_PARAMETER, self::REMOVE_PARAMETER, self::RENAME_PARAMETER], true);
    }

    /** @param array<string, mixed> $change */
    private function refactorMethod(ClassMethod $method, array $change): bool
    {
        if ($change['kind'] === self::NARROW_RETURN_TYPE) {
            return $this->narrowReturnType($method, $change);
        }

        if ($change['kind'] === self::WIDEN_PARAMETER_TYPE) {
            return $this->widenParameterType($method, $change);
        }

        if ($change['kind'] === self::ADD_OPTIONAL_PARAMETER) {
            return $this->addParameter($method, $change, true);
        }

        if ($change['kind'] === self::ADD_REQUIRED_PARAMETER) {
            return $this->addRequiredParameter($method, $change);
        }

        if ($change['kind'] === self::REMOVE_PARAMETER) {
            return $this->removeUnusedParameter($method, $change);
        }

        return $change['kind'] === self::RENAME_PARAMETER && $this->renameParameter($method, $change);
    }

    /** @param array<string, mixed> $change */
    private function narrowReturnType(ClassMethod $method, array $change): bool
    {
        $type = NativeTypeParser::parse($change['type']);
        if ($method->returnType !== null && $this->nodeComparator->areNodesEqual($method->returnType, $type)) {
            return false;
        }
        if (!$this->matchesCurrentType($method->returnType, $change['currentType'])) {
            return false;
        }

        $method->returnType = $type;

        return true;
    }

    /** @param array<string, mixed> $change */
    private function widenParameterType(ClassMethod $method, array $change): bool
    {
        $type = NativeTypeParser::parse($change['type']);

        foreach ($method->params as $parameter) {
            if (!$this->isName($parameter->var, $change['parameter'])) {
                continue;
            }
            if ($parameter->type !== null && $this->nodeComparator->areNodesEqual($parameter->type, $type)) {
                return false;
            }
            if (!$this->matchesCurrentType($parameter->type, $change['currentType'])) {
                return false;
            }

            $parameter->type = $type;

            return true;
        }

        return false;
    }

    /** @param array<string, mixed> $change */
    private function addParameter(ClassMethod $method, array $change, bool $optional): bool
    {
        foreach ($method->params as $parameter) {
            if ($this->isName($parameter->var, $change['parameter'])) {
                return false;
            }
        }

        $parameter = new Param(
            new Variable($change['parameter']),
            $optional ? BuilderHelpers::normalizeValue($change['default']) : null,
            NativeTypeParser::parse($change['type']),
        );
        array_splice($method->params, min($change['position'], count($method->params)), 0, [$parameter]);

        return true;
    }

    /** @param array<string, mixed> $change */
    private function addRequiredParameter(ClassMethod $method, array $change): bool
    {
        if (!$this->addParameter($method, $change, false)) {
            return false;
        }

        foreach ($method->stmts ?? [] as $position => $statement) {
            if (!$statement instanceof Expression
                || !$statement->expr instanceof Assign
                || !$this->isName($statement->expr->var, $change['parameter'])
                || !$this->isFuncGetArg($statement->expr->expr, $change['position'])
            ) {
                continue;
            }

            $statements = $method->stmts ?? [];
            unset($statements[$position]);
            $method->stmts = array_values($statements);
        }

        $this->traverseNodesWithCallable($method->stmts ?? [], function (Node $node) use ($change): int|Node|null {
            if ($node instanceof Closure || $node instanceof ArrowFunction) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }

            return $this->isFuncGetArg($node, $change['position']) ? new Variable($change['parameter']) : null;
        });

        return true;
    }

    private function isFuncGetArg(Node $node, int $position): bool
    {
        return $node instanceof FuncCall
            && $this->isName($node->name, 'func_get_arg')
            && count($node->getArgs()) === 1
            && $node->getArgs()[0]->value instanceof Int_
            && $node->getArgs()[0]->value->value === $position;
    }

    /** @param array<string, mixed> $change */
    private function removeUnusedParameter(ClassMethod $method, array $change): bool
    {
        foreach ($method->params as $position => $parameter) {
            if (!$this->isName($parameter->var, $change['parameter'])
                || $parameter->flags !== 0
                || $parameter->attrGroups !== []
                || str_contains($method->getDocComment()?->getText() ?? '', '$' . $change['parameter'])
                || $this->methodUsesVariable($method, $change['parameter'])
            ) {
                continue;
            }

            array_splice($method->params, $position, 1);

            return true;
        }

        return false;
    }

    /** @param array<string, mixed> $change */
    private function renameParameter(ClassMethod $method, array $change): bool
    {
        if (str_contains($method->getDocComment()?->getText() ?? '', '$' . $change['parameter'])
            || $this->methodUsesVariable($method, $change['newName'])
            || $this->containsNestedScope($method)
        ) {
            return false;
        }

        foreach ($method->params as $parameter) {
            if (!$parameter->var instanceof Variable
                || !$this->isName($parameter->var, $change['parameter'])
                || $parameter->flags !== 0
            ) {
                continue;
            }

            $parameter->var->name = $change['newName'];
            $this->traverseNodesWithCallable($method->stmts ?? [], static function (Node $node) use ($change): ?Node {
                if ($node instanceof Variable && $node->name === $change['parameter']) {
                    $node->name = $change['newName'];

                    return $node;
                }

                return null;
            });

            return true;
        }

        return false;
    }

    private function methodUsesVariable(ClassMethod $method, string $name): bool
    {
        return (new NodeFinder())->findFirst(
            $method->stmts ?? [],
            static fn (Node $node): bool => $node instanceof Variable && $node->name === $name,
        ) !== null;
    }

    private function containsNestedScope(ClassMethod $method): bool
    {
        return (new NodeFinder())->findFirstInstanceOf($method->stmts ?? [], Closure::class) !== null
            || (new NodeFinder())->findFirstInstanceOf($method->stmts ?? [], ArrowFunction::class) !== null;
    }

    private function refactorCall(MethodCall|NullsafeMethodCall|StaticCall|New_ $node): ?Node
    {
        foreach ($this->changes as $change) {
            if (!$this->matchesCall($node, $change)) {
                continue;
            }

            $changed = match ($change['kind']) {
                self::EXPLICIT_CURRENT_DEFAULT => $this->makeCurrentDefaultExplicit($node, $change),
                self::RENAME_PARAMETER => $this->renameCallArgument($node, $change),
                self::REMOVE_PARAMETER => $this->minimumIncludes($change) && $this->removeCallArgument($node, $change),
                default => false,
            };

            if ($changed) {
                return $node;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $change */
    private function makeCurrentDefaultExplicit(MethodCall|NullsafeMethodCall|StaticCall|New_ $node, array $change): bool
    {
        foreach ($node->getArgs() as $position => $argument) {
            if ($argument->unpack || $argument->name?->toString() === $change['parameter'] || ($argument->name === null && $position === $change['position'])) {
                return false;
            }
        }

        $node->args[] = new Arg(
            BuilderHelpers::normalizeValue($change['default']),
            name: new Identifier($change['parameter']),
        );

        return true;
    }

    /** @param array<string, mixed> $change */
    private function renameCallArgument(MethodCall|NullsafeMethodCall|StaticCall|New_ $node, array $change): bool
    {
        foreach ($node->getArgs() as $argumentPosition => $argument) {
            if ($argument->name?->toString() !== $change['parameter']) {
                continue;
            }

            if ($this->minimumIncludes($change)) {
                $argument->name = new Identifier($change['newName']);

                return true;
            }

            $argumentsBefore = array_slice($node->args, 0, $argumentPosition);
            if (count($argumentsBefore) > $change['position']) {
                return false;
            }

            foreach ($argumentsBefore as $argumentBefore) {
                if (!$argumentBefore instanceof Arg || $argumentBefore->name !== null || $argumentBefore->unpack) {
                    return false;
                }
            }

            $defaults = [];
            for ($position = count($argumentsBefore); $position < $change['position']; $position++) {
                $parameter = $change['parametersBefore'][$position];
                if (!$parameter['hasDefault']) {
                    return false;
                }
                $defaults[] = new Arg(BuilderHelpers::normalizeValue($parameter['default']));
            }

            array_splice($node->args, $argumentPosition, 0, $defaults);
            $renamedArgument = $node->args[$argumentPosition + count($defaults)] ?? null;
            if (!$renamedArgument instanceof Arg) {
                return false;
            }
            $renamedArgument->name = null;

            return true;
        }

        return false;
    }

    /** @param array<string, mixed> $change */
    private function minimumIncludes(array $change): bool
    {
        return $this->versions->minimumIsAtLeast(ltrim((string) $change['version'], 'v'));
    }

    /** @param array<string, mixed> $change */
    private function removeCallArgument(MethodCall|NullsafeMethodCall|StaticCall|New_ $node, array $change): bool
    {
        $positional = 0;

        foreach ($node->getArgs() as $argumentPosition => $argument) {
            if ($argument->unpack) {
                return false;
            }

            $matches = $argument->name?->toString() === $change['parameter']
                || ($argument->name === null && $positional === $change['position']);
            if ($argument->name === null) {
                $positional++;
            }

            if (!$matches || $argument->byRef || !$this->isSafeToDrop($argument->value)) {
                continue;
            }

            array_splice($node->args, $argumentPosition, 1);

            return true;
        }

        return false;
    }

    private function isSafeToDrop(Expr $expression): bool
    {
        if ($expression instanceof Scalar
            || $expression instanceof Variable
            || $expression instanceof ConstFetch
            || $expression instanceof ClassConstFetch
        ) {
            return true;
        }

        if ($expression instanceof UnaryMinus || $expression instanceof UnaryPlus) {
            return $this->isSafeToDrop($expression->expr);
        }

        if (!$expression instanceof Array_) {
            return false;
        }

        foreach ($expression->items as $item) {
            if (!$item instanceof ArrayItem
                || $item->unpack
                || !$this->isSafeToDrop($item->value)
                || ($item->key !== null && !$this->isSafeToDrop($item->key))
            ) {
                return false;
            }
        }

        return true;
    }

    /** @param array<string, mixed> $change */
    private function matchesCall(MethodCall|NullsafeMethodCall|StaticCall|New_ $node, array $change): bool
    {
        if (($node instanceof MethodCall || $node instanceof NullsafeMethodCall)
            && $this->isName($node->name, $change['method'])
        ) {
            return $this->isObjectType($node->var, new ObjectType($change['class']));
        }

        if (($node instanceof StaticCall || $node instanceof New_)
            && $this->isObjectType($node->class, new ObjectType($change['class']))
        ) {
            return $change['method'] === ($node instanceof New_ ? '__construct' : $this->getName($node->name));
        }

        return false;
    }

    private function matchesCurrentType(Identifier|Name|ComplexType|null $type, mixed $currentType): bool
    {
        if ($currentType === null) {
            return $type === null;
        }

        return is_string($currentType)
            && $type !== null
            && $this->nodeComparator->areNodesEqual($type, NativeTypeParser::parse($currentType));
    }
}
