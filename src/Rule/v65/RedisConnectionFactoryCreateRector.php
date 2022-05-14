<?php

namespace Frosh\Rector\Rule\v65;

use PhpParser\Node;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function var_dump;

class RedisConnectionFactoryCreateRector extends AbstractRector
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new RuleDefinition('test', []);
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
        if (! $this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType(
            $node,
            new ObjectType('Shopware\Core\Framework\Adapter\Cache\RedisConnectionFactory')
        )) {
            return null;
        }

        if (! $this->isName($node->name, 'createConnection')) {
            return null;
        }

        $factoryAssign = new Node\Expr\Assign(
            new Node\Expr\Variable('redisFactory'),
            new Node\Expr\New_(new Node\Name('Shopware\Core\Framework\Adapter\Cache\RedisConnectionFactory')),
        );

        $this->nodesToAddCollector->addNodeBeforeNode($factoryAssign, $node);

        return $this->nodeFactory->createMethodCall(
            new Node\Expr\Variable('redisFactory'),
            'create',
            $node->args
        );
    }
}