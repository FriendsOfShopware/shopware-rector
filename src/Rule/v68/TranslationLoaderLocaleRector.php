<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PHPStan\Type\ObjectType;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class TranslationLoaderLocaleRector extends AbstractRector
{
    private const LOADER = 'Shopware\Core\System\Snippet\Service\AbstractTranslationLoader';

    public function __construct(private readonly BetterNodeFinder $betterNodeFinder) {}

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add locale awareness to translation loader overrides', [new CodeSample(
            'public function pluginTranslationExists(Plugin $plugin): bool { return $this->getDecorated()->pluginTranslationExists($plugin); }',
            'public function pluginTranslationExistsForLocale(Plugin $plugin, string $locale): bool { return $this->getDecorated()->pluginTranslationExistsForLocale($plugin, locale: $locale); }',
        )]);
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /** @param Class_ $node */
    public function refactor(Node $node): ?Node
    {
        $method = $node->getMethod('pluginTranslationExists');
        if ($method === null || count($method->params) !== 1 || $method->params[0]->variadic || $node->getMethod('pluginTranslationExistsForLocale') !== null || !$this->isObjectType($node, new ObjectType(self::LOADER))) {
            return null;
        }
        if ($this->betterNodeFinder->findFirst($method, fn (Node $inner): bool => $inner instanceof Variable && $this->isName($inner, 'locale')) !== null) {
            return null;
        }
        $method->name = new Identifier('pluginTranslationExistsForLocale');
        $method->params[] = new Param(new Variable('locale'), type: new Identifier('string'));
        $this->traverseNodesWithCallable($method->stmts ?? [], function (Node $inner): Node|int|null {
            if ($inner instanceof Node\FunctionLike || $inner instanceof Node\Stmt\ClassLike) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
            if ((!$inner instanceof MethodCall && !$inner instanceof StaticCall) || !$this->isName($inner->name, 'pluginTranslationExists') || count($inner->args) !== 1) {
                return null;
            }
            if ($inner instanceof StaticCall ? !$this->isName($inner->class, 'parent') : !$this->isObjectType($inner->var, new ObjectType(self::LOADER))) {
                return null;
            }
            $arg = $inner->args[0];
            if (!$arg instanceof Arg || $arg->unpack) {
                return null;
            }
            $inner->name = new Identifier('pluginTranslationExistsForLocale');
            $inner->args[] = new Arg(new Variable('locale'), name: new Identifier('locale'));

            return $inner;
        });

        return $node;
    }
}
