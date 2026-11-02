<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\Renaming\Rector\StaticCall;

use Frosh\Rector\Rule\Renaming\ValueObject\RenameStaticMethod;
use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Webmozart\Assert\Assert;

final class RenameStaticMethodRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var RenameStaticMethod[]
     */
    private array $staticMethodRenames = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turn static method names to new ones', [
            new ConfiguredCodeSample(
                'SomeClass::oldStaticMethod();',
                'AnotherExampleClass::newStaticMethod();',
                [new RenameStaticMethod('SomeClass', 'oldStaticMethod', 'AnotherExampleClass', 'newStaticMethod')],
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->staticMethodRenames as $staticMethodRename) {
            if (!$this->isName($node->name, $staticMethodRename->getOldMethod())) {
                continue;
            }

            if (!$this->isObjectType($node->class, $staticMethodRename->getOldObjectType())) {
                continue;
            }

            return $this->rename($node, $staticMethodRename);
        }

        return null;
    }

    /**
     * @param RenameStaticMethod[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsInstanceOf($configuration, RenameStaticMethod::class);
        $this->staticMethodRenames = array_values($configuration);
    }

    private function rename(StaticCall $staticCall, RenameStaticMethod $renameStaticMethod): StaticCall
    {
        $staticCall->name = new Identifier($renameStaticMethod->getNewMethod());

        if ($renameStaticMethod->hasClassChanged()) {
            $staticCall->class = new FullyQualified($renameStaticMethod->getNewClass());
        }

        return $staticCall;
    }
}
