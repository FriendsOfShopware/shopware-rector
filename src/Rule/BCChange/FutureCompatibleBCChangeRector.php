<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\BCChange;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\NullsafeMethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class FutureCompatibleBCChangeRector extends AbstractRector implements ConfigurableRectorInterface
{
    public const ADD_OPTIONAL_PARAMETER = 'add_optional_parameter';
    public const WIDEN_PARAMETER_TYPE = 'widen_parameter_type';
    public const NARROW_RETURN_TYPE = 'narrow_return_type';
    public const EXPLICIT_CURRENT_DEFAULT = 'explicit_current_default';

    /** @var list<array<string, mixed>> */
    private array $configuration = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Apply forward-compatible declaration and call-site changes generated from Shopware BC-change attributes.', [
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

    /** @param list<array<string, mixed>> $configuration */
    public function configure(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    private function refactorClass(Class_ $class): ?Class_
    {
        $changed = false;

        foreach ($this->configuration as $change) {
            if (!in_array($change['kind'], [self::ADD_OPTIONAL_PARAMETER, self::WIDEN_PARAMETER_TYPE, self::NARROW_RETURN_TYPE], true)
                || !$this->isObjectType($class, new ObjectType($change['class']))
            ) {
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
    private function refactorMethod(ClassMethod $method, array $change): bool
    {
        $type = NativeTypeParser::parse($change['type']);

        if ($change['kind'] === self::NARROW_RETURN_TYPE) {
            if ($method->returnType !== null && $this->nodeComparator->areNodesEqual($method->returnType, $type)) {
                return false;
            }
            if (!$this->matchesCurrentType($method->returnType, $change['currentType'])) {
                return false;
            }

            $method->returnType = $type;

            return true;
        }

        if ($change['kind'] === self::WIDEN_PARAMETER_TYPE) {
            foreach ($method->params as $parameter) {
                if ($this->isName($parameter->var, $change['parameter'])) {
                    if ($parameter->type !== null && $this->nodeComparator->areNodesEqual($parameter->type, $type)) {
                        return false;
                    }
                    if (!$this->matchesCurrentType($parameter->type, $change['currentType'])) {
                        return false;
                    }

                    $parameter->type = $type;

                    return true;
                }
            }

            return false;
        }

        foreach ($method->params as $parameter) {
            if ($this->isName($parameter->var, $change['parameter'])) {
                return false;
            }
        }

        $parameter = new Param(new Variable($change['parameter']), BuilderHelpers::normalizeValue($change['default']), $type);
        array_splice($method->params, min($change['position'], count($method->params)), 0, [$parameter]);

        return true;
    }

    private function refactorCall(MethodCall|NullsafeMethodCall|StaticCall|New_ $node): ?Node
    {
        foreach ($this->configuration as $change) {
            if ($change['kind'] !== self::EXPLICIT_CURRENT_DEFAULT || !$this->matchesCall($node, $change)) {
                continue;
            }

            foreach ($node->getArgs() as $position => $argument) {
                if ($argument->unpack || $argument->name?->toString() === $change['parameter'] || ($argument->name === null && $position === $change['position'])) {
                    return null;
                }
            }

            $node->args[] = new Arg(
                BuilderHelpers::normalizeValue($change['default']),
                name: new Identifier($change['parameter']),
            );

            return $node;
        }

        return null;
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

    private function matchesCurrentType(Identifier|Node\Name|Node\ComplexType|null $type, mixed $currentType): bool
    {
        if ($currentType === null) {
            return $type === null;
        }

        return is_string($currentType)
            && $type !== null
            && $this->nodeComparator->areNodesEqual($type, NativeTypeParser::parse($currentType));
    }
}
