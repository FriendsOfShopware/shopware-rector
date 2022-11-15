<?php

namespace Frosh\Rector\Rule\v65;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class ShopwareRequestInitializedRector extends AbstractRector
{
    use ShopwareRequestTrait;

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace retrieving Shopware-related attributes from $request by ShopwareRequest', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$salesChannelId = $request->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID, '');
CODE_SAMPLE
                ,
                <<<'PHP'
$shopware = $request->attributes->get(ShopwareRequest::ATTRIBUTE_REQUEST);
$salesChannelId = $shopware->saleChannelId;
PHP
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node)
    {
        $swRequest = $this->betterNodeFinder->findFirst($node, function (Node $subNode): bool
        {
            try {
                return $this->isObjectType($subNode, new ObjectType('Shopware\\Storefront\\Framework\\Routing\\ShopwareRequest'));
            } catch (\Exception $e) {
                return false;
            }
        });
        if ($swRequest !== null) {
            return null;
        }

        /** @var MethodCall[] $methodCall */
        $methodCall = $this->betterNodeFinder->findInstanceOf($node, MethodCall::class);
        if (!count($methodCall)) {
            return null;
        }

        /** @var Variable|null $isFoundInStmt */
        $isFoundInStmt = null;
        foreach ($methodCall as $method) {
            if ($isFoundInStmt = $this->havingSwRequestTransformed($method)) {
                break;
            }
        }

        if (!$isFoundInStmt instanceof Variable) {
            return null;
        }

        return $this->injectShopwareRequest($node, $isFoundInStmt);
    }

    private function injectShopwareRequest(ClassMethod $node, Variable $isFoundInStmt): ClassMethod
    {
        $request = $this->betterNodeFinder->findFirst($node, function (Node $subNode): bool
        {
            return $subNode instanceof Variable && $this->isObjectType($subNode, new ObjectType('Symfony\\Component\\HttpFoundation\\Request'));
        });

        $currentStmt = $this->betterNodeFinder->resolveCurrentStatement($request);

        $position = 0;
        foreach ($node->stmts as $stmt) {
            if ($this->nodeComparator->areNodesEqual($stmt, $currentStmt)) {
                break;
            }
            $position++;
        }
        array_splice($node->stmts, $position, 0, [$this->createShopwareRequest($isFoundInStmt->name)]);

        return $node;
    }

    private function createShopwareRequest(string $swName): Node\Stmt
    {
        return new Expression(
            new Assign(
                new Variable('shopware'),
                new MethodCall(new PropertyFetch(new Variable($swName), 'attributes'), 'get', [
                    new Arg(new ClassConstFetch(new FullyQualified('Shopware\\Storefront\\Framework\\Routing\\ShopwareRequest'), 'ATTRIBUTE_REQUEST')),
                ])
            )
        );
    }
}
