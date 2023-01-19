<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class AddArgumentToClassWithoutDefaultRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var AddArgumentToClassWithoutDefault[]
     */
    protected array $configuration = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'This Rector adds new default arguments in calls of defined methods and class types.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->someMethod();

class MyCustomClass extends SomeExampleClass
{
    public function someMethod()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->someMethod(true);

class MyCustomClass extends SomeExampleClass
{
    public function someMethod($value)
    {
    }
}
CODE_SAMPLE
                    ,
                    [
                        new AddArgumentToClassWithoutDefault('SomeExampleClass', 'someMethod', 0, 'someArgument', new ObjectType('SomeType')), ]
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [
            ClassMethod::class,
        ];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node)
    {
        $hasChanged = false;

        foreach ($this->configuration as $config) {
            if (!$this->isObjectTypeMatch($node, $config->getObjectType())) {
                continue;
            }
            if (!$this->isName($node->name, $config->getMethod())) {
                continue;
            }

            $param = new Param(new Variable($config->getName()));

            if ($config->getType()) {
                $param->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($config->getType(), TypeKind::PARAM);
            }

            $node->params[$config->getPosition()] = $param;
            $hasChanged = true;
        }

        if ($hasChanged) {
            return $node;
        }

        return null;
    }


    public function configure(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    private function isObjectTypeMatch($node, ObjectType $objectType): bool
    {
        if ($node instanceof MethodCall) {
            return $this->isObjectType($node->var, $objectType);
        }
        if ($node instanceof StaticCall) {
            return $this->isObjectType($node->class, $objectType);
        }
        $classLike = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$classLike instanceof Class_) {
            return false;
        }
        return $this->isObjectType($classLike, $objectType);
    }
}
