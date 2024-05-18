<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\Class_;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class InterfaceReplacedWithAbstractClassRector extends AbstractRector implements ConfigurableRectorInterface
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
                    CODE_SAMPLE
                ,
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
        ];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->implements === []) {
            return null;
        }

        $hasChanged = false;

        foreach ($this->configuration as $config) {
            $foundIt = false;
            foreach ($node->implements as $key => $implement) {
                if ($this->isObjectType($implement, $config->getInterfaceObject())) {
                    $foundIt = true;
                    unset($node->implements[$key]);
                }
            }

            if ($foundIt) {
                $node->extends = new Name($config->getAbstractClass());
                $hasChanged = true;
            }
        }

        if ($hasChanged) {
            return $node;
        }

        return null;
    }

    /**
     * @param InterfaceReplacedWithAbstractClass[] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->configuration = $configuration;
    }
}
