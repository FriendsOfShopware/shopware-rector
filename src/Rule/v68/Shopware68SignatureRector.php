<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\NodeTraverser;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\PhpParser\Node\BetterNodeFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class Shopware68SignatureRector extends AbstractRector
{
    private const SIGNATURES = [
        ['Shopware\Core\Checkout\Cart\SalesChannel\AbstractCartLoadRoute', 'load', 2, 'cart', 'Shopware\Core\Checkout\Cart\Cart', 'null'],
        ['Shopware\Core\Content\Media\Thumbnail\ThumbnailService', 'updateThumbnails', 3, 'force', 'bool', 'false'],
        ['Shopware\Storefront\Theme\ThemeService', 'compileTheme', 5, 'notifyOnAsyncCompletion', 'bool', 'true'],
        ['Shopware\Core\Framework\RateLimiter\RateLimiter', 'ensureAccepted', 2, 'salesChannelId', 'string', 'null'],
        ['Shopware\Core\Framework\RateLimiter\RateLimiterFactory', 'create', 1, 'salesChannelId', 'string', 'null'],
    ];

    public function __construct(private readonly BetterNodeFinder $betterNodeFinder) {}

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add new optional Shopware parameters to overrides and forward them to decorated/parent calls', [new CodeSample(
            'class MyLimiter extends RateLimiter { public function ensureAccepted(string $route, string $key): void {} }',
            'class MyLimiter extends RateLimiter { public function ensureAccepted(string $route, string $key, ?string $salesChannelId = null): void {} }',
        )]);
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /** @param Class_ $node */
    public function refactor(Node $node): ?Node
    {
        $changed = false;
        if ($this->isObjectType($node, new ObjectType('Shopware\Core\Checkout\Document\DocumentEntity'))) {
            foreach (['setOrderId', 'setOrderVersionId'] as $setter) {
                $method = $node->getMethod($setter);
                $parameter = $method?->params[0] ?? null;
                if ($parameter !== null && $parameter->type instanceof Identifier && $parameter->type->toString() === 'string') {
                    $parameter->type = new NullableType(new Identifier('string'));
                    $changed = true;
                }
            }
        }
        foreach (self::SIGNATURES as [$class, $methodName, $position, $name, $type, $default]) {
            $method = $node->getMethod($methodName);
            if ($method === null || !$this->isObjectType($node, new ObjectType($class)) || count($method->params) !== $position) {
                continue;
            }
            foreach ($method->params as $param) {
                if ($param->variadic || $this->isName($param->var, $name)) {
                    continue 2;
                }
            }
            // Do not shadow an existing local variable with the new parameter.
            if ($this->betterNodeFinder->findFirst($method->stmts ?? [], fn (Node $inner): bool => $inner instanceof Variable && $this->isName($inner, $name)) !== null) {
                continue;
            }

            $typeNode = in_array($type, ['bool', 'string'], true) ? new Identifier($type) : new FullyQualified($type);
            $method->params[] = new Param(new Variable($name), new ConstFetch(new Name($default)), $default === 'null' ? new NullableType($typeNode) : $typeNode);
            $this->traverseNodesWithCallable($method->stmts ?? [], function (Node $inner) use ($class, $methodName, $position, $name): ?int {
                if ($inner instanceof Node\FunctionLike || $inner instanceof Node\Stmt\ClassLike) {
                    return NodeTraverser::DONT_TRAVERSE_CHILDREN;
                }
                if (!$inner instanceof MethodCall && !$inner instanceof StaticCall) {
                    return null;
                }
                if (!$this->isName($inner->name, $methodName) || count($inner->args) !== $position) {
                    return null;
                }
                if ($inner instanceof StaticCall ? !$this->isName($inner->class, 'parent') : !$this->isObjectType($inner->var, new ObjectType($class))) {
                    return null;
                }
                foreach ($inner->args as $arg) {
                    if (!$arg instanceof Arg || $arg->unpack || $arg->name?->toString() === $name) {
                        return null;
                    }
                }
                $inner->args[] = new Arg(new Variable($name), name: new Identifier($name));

                return null;
            });
            $changed = true;
        }

        return $changed ? $node : null;
    }
}
