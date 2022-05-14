<?php

namespace Frosh\Rector\Rule\v65;

use PhpParser\Node;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class ThemeCompilerPrefixRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Removes ThemeCompiler::getThemePrefix deprecation', [
            new CodeSample(
                <<<'CODE_SAMPLE'
\Shopware\Storefront\Theme\ThemeCompiler::getThemePrefix('bla');
CODE_SAMPLE
                ,
                <<<'PHP'
$prefixBuilder = new \Shopware\Storefront\Theme\MD5ThemePathBuilder();
$prefixBuilder->assemblePath('bla');
PHP
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [
            Node\Expr\StaticCall::class
        ];
    }

    /**
     * @param Node\Expr\StaticCall $node
     */
    public function refactor(Node $node)
    {
        if (! $this->isName($node->name, 'getThemePrefix')) {
            return null;
        }

        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('Shopware\Storefront\Theme\ThemeCompiler'))) {
            return null;
        }

        $assign = new Node\Expr\Assign(
            new Node\Expr\Variable('themePrefixer'),
            new Node\Expr\New_(new Node\Name('Shopware\Storefront\Theme\MD5ThemePathBuilder'))
        );

        $this->nodesToAddCollector->addNodeBeforeNode($assign, $node);

        return new Node\Expr\MethodCall(new Node\Expr\Variable('themePrefixer'), 'assemblePath', $node->args);
    }
}