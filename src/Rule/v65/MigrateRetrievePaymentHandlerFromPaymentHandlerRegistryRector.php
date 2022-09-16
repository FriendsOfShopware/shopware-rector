<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v65;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use PHPStan\Type\ObjectType;

class MigrateRetrievePaymentHandlerFromPaymentHandlerRegistryRector extends AbstractRector
{
    private const CLASS_TO_REFACTOR = 'Shopware\Core\Checkout\Payment\Cart\PaymentHandler\PaymentHandlerRegistry';
    private const OLD_METHOD_NAME_SYNC = 'getSyncHandlerForPaymentMethod';
    private const NEW_METHOD_NAME_SYNC = 'getSyncPaymentHandler';
    private const OLD_METHOD_NAME_ASYNC = 'getAsyncHandlerForPaymentMethod';
    private const NEW_METHOD_NAME_ASYNC = 'getAsyncPaymentHandler';
    private const OLD_METHOD_NAME_PREPARED = 'getPreparedHandlerForPaymentMethod';
    private const NEW_METHOD_NAME_PREPARED = 'getPreparedPaymentHandler';
    private const OLD_METHOD_NAME_REFUND = 'getRefundHandlerForPaymentMethod';
    private const NEW_METHOD_NAME_REFUND = 'getRefundPaymentHandler';
    private const OLD_METHOD_NAME_PAYMENT = 'getHandlerForPaymentMethod';
    private const NEW_METHOD_NAME_PAYMENT = 'getPaymentMethodHandler';
    private const GET_ID_METHOD_NAME = 'getId';

    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new RuleDefinition('Migrates Annotations to Route annotation', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$paymentMethod = $this->getPaymentMethod('someHandlerName');
$handler = $paymentHandlerRegistry->getSyncHandlerForPaymentMethod($paymentMethod);
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$paymentMethod = $this->getPaymentMethod('someHandlerName');
$handler = $paymentHandlerRegistry->getSyncPaymentHandler($paymentMethod->getId());
CODE_SAMPLE
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [
            \PhpParser\Node\Expr\MethodCall::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType(
            $node,
            new ObjectType(static::CLASS_TO_REFACTOR)
        )) {
            return null;
        }
        if (!$this->isName($node->name, static::OLD_METHOD_NAME_SYNC)
            && !$this->isName($node->name, static::OLD_METHOD_NAME_ASYNC)
            && !$this->isName($node->name, static::OLD_METHOD_NAME_PREPARED)
            && !$this->isName($node->name, static::OLD_METHOD_NAME_REFUND)
            && !$this->isName($node->name, static::OLD_METHOD_NAME_PAYMENT)
        ) {
            return null;
        }

        if ($this->isName($node->name, static::OLD_METHOD_NAME_SYNC)) {
            $node->name = new \PhpParser\Node\Identifier(static::NEW_METHOD_NAME_SYNC);
        }
        if ($this->isName($node->name, static::OLD_METHOD_NAME_ASYNC)){
            $node->name = new \PhpParser\Node\Identifier(static::NEW_METHOD_NAME_ASYNC);
        }
        if ($this->isName($node->name, static::OLD_METHOD_NAME_PREPARED)){
            $node->name = new \PhpParser\Node\Identifier(static::NEW_METHOD_NAME_PREPARED);
        }
        if ($this->isName($node->name, static::OLD_METHOD_NAME_REFUND)){
            $node->name = new \PhpParser\Node\Identifier(static::NEW_METHOD_NAME_REFUND);
        }
        if ($this->isName($node->name, static::OLD_METHOD_NAME_PAYMENT)){
            $node->name = new \PhpParser\Node\Identifier(static::NEW_METHOD_NAME_PAYMENT);
        }

        // add the method call to get the id for the first argument
        $node->args[0] = new Node\Expr\MethodCall($node->args[0]->value, static::GET_ID_METHOD_NAME);

        return $node;
    }
}