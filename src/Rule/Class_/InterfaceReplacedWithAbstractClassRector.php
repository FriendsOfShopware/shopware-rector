<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\Class_;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class InterfaceReplacedWithAbstractClassRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var InterfaceReplacedWithAbstractClass[]
     */
    protected array $configuration = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace UrlProviderInterface with AbstractClass', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
                    class Foo implements Test {

                    }
                    CODE_SAMPLE,
                <<<'PHP'
                    class Foo extends AbstractTest {

                    }
                    PHP,
                [new InterfaceReplacedWithAbstractClass('Foo', 'AbstractTest')],
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [
            Class_::class,
            Property::class,
            Param::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        $hasChanged = false;

        foreach ($this->configuration as $config) {
            if ($node instanceof Class_) {
                $hasChanged = $this->refactorClass($node, $config) || $hasChanged;

                continue;
            }

            if ((!$node instanceof Property && !$node instanceof Param) || $node->type === null) {
                continue;
            }

            $type = $this->replaceType($node->type, $config);
            if ($type !== null) {
                $node->type = $type;
                $hasChanged = true;
            }
        }

        return $hasChanged ? $node : null;
    }

    /**
     * @param InterfaceReplacedWithAbstractClass[] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    private function refactorClass(Class_ $class, InterfaceReplacedWithAbstractClass $config): bool
    {
        $implementsInterface = false;
        foreach ($class->implements as $implement) {
            if ($this->isConfiguredInterface($implement, $config)) {
                $implementsInterface = true;
            }
        }

        if (!$implementsInterface || $this->isName($class, ltrim($config->getAbstractClass(), '\\'))) {
            return false;
        }

        // A class that already extends another class can only drop the interface if that class extends the abstract class
        if ($class->extends !== null && !$this->isObjectType($class->extends, $config->getAbstractClassObject())) {
            return false;
        }

        $class->implements = array_values(array_filter(
            $class->implements,
            fn (Name $implement): bool => !$this->isConfiguredInterface($implement, $config),
        ));

        $class->extends ??= new Name($config->getAbstractClass());

        return true;
    }

    /**
     * Returns the new type, or null if the type does not contain the interface.
     */
    private function replaceType(Identifier|Name|ComplexType $type, InterfaceReplacedWithAbstractClass $config): Identifier|Name|ComplexType|null
    {
        if ($type instanceof Name) {
            return $this->isConfiguredInterface($type, $config) ? new Name($config->getAbstractClass()) : null;
        }

        if ($type instanceof NullableType) {
            $innerType = $this->replaceType($type->type, $config);

            return $innerType instanceof Identifier || $innerType instanceof Name ? new NullableType($innerType) : null;
        }

        if (!$type instanceof UnionType) {
            return null;
        }

        $containsInterface = false;
        $containsAbstractClass = false;
        foreach ($type->types as $subType) {
            if ($subType instanceof Name && $this->isConfiguredInterface($subType, $config)) {
                $containsInterface = true;
            } elseif ($subType instanceof Name && $this->isName($subType, ltrim($config->getAbstractClass(), '\\'))) {
                $containsAbstractClass = true;
            }
        }

        if (!$containsInterface) {
            return null;
        }

        $types = [];
        foreach ($type->types as $subType) {
            if (!$subType instanceof Name || !$this->isConfiguredInterface($subType, $config)) {
                $types[] = $subType;
            } elseif (!$containsAbstractClass) {
                // Keep the position of the interface in the union
                $types[] = new Name($config->getAbstractClass());
                $containsAbstractClass = true;
            }
        }

        return \count($types) === 1 ? $types[0] : new UnionType($types);
    }

    /**
     * Matches the configured interface by name only, so types of classes implementing it stay untouched.
     */
    private function isConfiguredInterface(Name $name, InterfaceReplacedWithAbstractClass $config): bool
    {
        return $this->isName($name, ltrim($config->getInterface(), '\\'));
    }
}
