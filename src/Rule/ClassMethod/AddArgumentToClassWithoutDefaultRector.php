<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class AddArgumentToClassWithoutDefaultRector extends AbstractRector implements ConfigurableRectorInterface
{
    public function __construct(private readonly StaticTypeMapper $staticTypeMapper)
    {
    }

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
                        new AddArgumentToClassWithoutDefault('SomeExampleClass', 'someMethod', 0, 'someArgument', new ObjectType('SomeType')), ],
                ),
            ],
        );
    }

    public function getNodeTypes(): array
    {
        return [
            Class_::class,
        ];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasChanged = false;

        foreach ($node->stmts as $method) {
            if ($method instanceof ClassMethod) {
                foreach ($this->configuration as $config) {
                    if (!$this->isObjectType($node, $config->getObjectType())) {
                        continue;
                    }
                    if (!$this->isName($method->name, $config->getMethod())) {
                        continue;
                    }

                    $param = new Param(new Variable($config->getName()));

                    if ($config->getType()) {
                        $param->type = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($config->getType(), TypeKind::PARAM);
                    }

                    $method->params[$config->getPosition()] = $param;
                    $hasChanged = true;
                }
            }
        }

        if ($hasChanged) {
            return $node;
        }

        return null;
    }

    /**
     * @param AddArgumentToClassWithoutDefault[] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->configuration = $configuration;
    }
}
