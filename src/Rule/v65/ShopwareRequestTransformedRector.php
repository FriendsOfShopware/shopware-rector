<?php

namespace Frosh\Rector\Rule\v65;

use PhpParser\Builder\Class_;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class ShopwareRequestTransformedRector extends AbstractRector
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node)
    {
        $isFoundInStmt = $this->havingSwRequestTransformed($node);
        if (!$isFoundInStmt instanceof Variable) {
            return null;
        }

        /** @var Node|ClassConstFetch $arg1 */
        $arg1 = $node->args[0]->value;
        $swAttr = $this->getTransformedShopwareAttr($arg1);

        return new PropertyFetch(new Variable('shopware'), $swAttr);
    }
}
