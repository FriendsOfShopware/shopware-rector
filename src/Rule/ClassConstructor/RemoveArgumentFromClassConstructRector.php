<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\ClassConstructor;

use Frosh\Rector\Rule\ClassMethod\AddArgumentToClassWithoutDefault;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class RemoveArgumentFromClassConstructRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var AddArgumentToClassWithoutDefault[]
     */
    protected array $configuration = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'This Rector removes an argument in the defined class construct.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass($example);
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass();
CODE_SAMPLE
,
                    [
                        new RemoveArgumentFromClassConstruct('SomeExampleClass', 0),
                    ]
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [
            New_::class,
        ];
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node)
    {
        $hasChanged = false;

        foreach ($this->configuration as $config) {
            if (!$this->isObjectType($node->class, $config->getObjectType())) {
                continue;
            }

            $args = $node->getArgs();
            if (!isset($args[$config->getPosition()])) {
                continue;
            }
            unset($node->args[$config->getPosition()]);
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
}
