<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\ClassConstructor;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ArrayType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class MakeClassConstructorArgumentRequiredRector extends AbstractRector implements ConfigurableRectorInterface
{
    public function __construct(private readonly StaticTypeMapper $typeMapper) {}

    /**
     * @var MakeClassConstructorArgumentRequired[]
     */
    protected array $configuration;

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('NAME', [
            new ConfiguredCodeSample(
                <<<'PHP'
                    class Foo {
                        public function __construct(array $foo = [])
                        {
                        }
                    }
                    PHP,
                <<<'PHP'
                    class Foo {
                        public function __construct(array $foo)
                        {
                        }
                    }
                    PHP,
                [new MakeClassConstructorArgumentRequired('Foo', 0, new ArrayType(new StringType(), new StringType()))],
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [
            Class_::class,
            New_::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Class_) {
            $changes = false;
            foreach ($node->stmts as $classMethod) {
                if (($classMethod instanceof ClassMethod) && $this->rebuildClassMethod($node, $classMethod)) {
                    $changes = true;
                }
            }

            if ($changes) {
                return $node;
            }

            return null;
        }

        if (!$node instanceof New_) {
            return null;
        }

        return $this->rebuildNew($node);
    }

    /**
     * @param MakeClassConstructorArgumentRequired[] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    private function rebuildClassMethod(Class_ $class, ClassMethod $node): bool
    {
        if (!$this->isName($node, '__construct')) {
            return false;
        }

        $hasModified = false;

        foreach ($this->configuration as $config) {
            if (!$this->isObjectType($class, $config->getClassObject())) {
                continue;
            }

            if (!isset($node->params[$config->getPosition()])) {
                continue;
            }

            $node->params[$config->getPosition()]->default = null;

            $hasModified = true;
        }

        if ($hasModified) {
            return true;
        }

        return false;
    }

    private function rebuildNew(New_ $node): ?Node
    {
        $hasModified = false;

        foreach ($this->configuration as $config) {
            if (!$this->isObjectType($node->class, $config->getClassObject())) {
                continue;
            }

            if (isset($node->args[$config->getPosition()])) {
                continue;
            }

            $defaultExpr = $this->createDefaultExpr($config->getDefault());
            if ($defaultExpr instanceof Expr) {
                $node->args[$config->getPosition()] = new Arg($defaultExpr);
            }

            $hasModified = true;
        }

        if ($hasModified) {
            return $node;
        }

        return null;
    }

    private function createDefaultExpr(?Type $defaultType): ?Expr
    {
        if ($defaultType === null) {
            return null;
        }

        if ($defaultType->isNull()->yes()) {
            return new ConstFetch(new Name('null'));
        }

        $mapped = $this->typeMapper->mapPHPStanTypeToPhpParserNode($defaultType, TypeKind::PARAM);

        return $mapped instanceof Expr ? $mapped : null;
    }
}
