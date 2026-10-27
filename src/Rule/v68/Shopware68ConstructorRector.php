<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\NodeAnalyzer\ExprAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\PhpParser\Node\Value\ValueResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class Shopware68ConstructorRector extends AbstractRector
{
    private const PARAMETERS = [
        'Shopware\Core\Checkout\Cart\CartBehavior' => ['permissions', 'hookAware', 'isRecalculation'],
        'Shopware\Core\Content\ProductExport\Struct\ProductExportResult' => ['content', 'errors', 'total', 'offset', 'hasNextBatch'],
        'Shopware\Core\Framework\Adapter\Cache\Http\CacheAttribute' => ['maxAge', 'sMaxAge', 'policyModifier', 'states'],
        'Shopware\Core\Framework\Api\OAuth\Client\ApiClient' => ['identifier', 'writeAccess', 'name', 'confidential', 'redirectUris', 'grantTypes'],
        'Shopware\Core\Content\Seo\HreflangLoaderParameter' => ['route', 'routeParameters', 'salesChannelContext', 'homepage', 'basePath'],
    ];

    public function __construct(private readonly ExprAnalyzer $exprAnalyzer, private readonly ValueResolver $valueResolver) {}

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrate changed Shopware 6.8 constructor arguments without guessing new context', [new CodeSample(
            "new ApiClient('id', true, 'Plugin');",
            "new ApiClient(identifier: 'id', writeAccess: true, name: 'Plugin', confidential: true);",
        )]);
    }

    public function getNodeTypes(): array
    {
        return [New_::class];
    }

    /** @param New_ $node */
    public function refactor(Node $node): ?Node
    {
        if (!$node->class instanceof Name) {
            return null;
        }

        $class = $this->getName($node->class);
        $parameters = self::PARAMETERS[$class] ?? null;
        if ($parameters === null) {
            return null;
        }

        $args = NamedArguments::resolve($node->args, $parameters);
        if ($args === null) {
            return null;
        }

        $removed = match ($class) {
            'Shopware\Core\Checkout\Cart\CartBehavior' => 'isRecalculation',
            'Shopware\Core\Content\ProductExport\Struct\ProductExportResult' => 'total',
            'Shopware\Core\Framework\Adapter\Cache\Http\CacheAttribute' => 'states',
            default => null,
        };
        if ($removed !== null) {
            if (!isset($args[$removed])) {
                return null;
            }
            $value = $args[$removed]->value;
            if (!($value instanceof Variable && is_string($value->name)) && $this->exprAnalyzer->isDynamicExpr($value)) {
                return null;
            }

            // A true recalculation flag requires selecting the applicable granular permission.
            if ($removed === 'isRecalculation' && !$this->valueResolver->isFalse($args[$removed]->value)) {
                return null;
            }

            unset($args[$removed]);
        } elseif ($class === 'Shopware\Core\Framework\Api\OAuth\Client\ApiClient') {
            if (!isset($args['confidential']) || $this->valueResolver->isNull($args['confidential']->value)) {
                $args['confidential'] = new Arg(new ConstFetch(new Name('true')), name: new Identifier('confidential'));
            } elseif (!$this->getType($args['confidential']->value)->isNull()->no() && !($args['confidential']->value instanceof Coalesce && $this->valueResolver->isTrue($args['confidential']->value->right))) {
                $args['confidential']->value = new Coalesce($args['confidential']->value, new ConstFetch(new Name('true')));
            }
        } else {
            if (!isset($args['route']) || !$this->valueResolver->isValue($args['route']->value, 'frontend.home.page')) {
                return null;
            }
            if (isset($args['homepage']) && !$this->valueResolver->isFalse($args['homepage']->value)) {
                return null;
            }
            $args['homepage'] = new Arg(new ConstFetch(new Name('true')), name: new Identifier('homepage'));
        }

        $replacement = clone $node;
        $replacement->args = array_values($args);
        if ($this->nodeComparator->areNodesEqual($node, $replacement)) {
            return null;
        }

        return $replacement;
    }
}
