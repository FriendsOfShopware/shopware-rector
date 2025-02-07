<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v65;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeFinder;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class AddBanAllToReverseProxyRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Adds banAll method to reverse proxy', [
            new CodeSample(
                <<<'CODE_SAMPLE'
                    class Test extends \Shopware\Core\Framework\Cache\ReverseProxy\AbstractReverseProxyGateway {

                    }
                    CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
                    class Test extends \Shopware\Core\Framework\Cache\ReverseProxy\AbstractReverseProxyGateway {
                        public function banAll(): void
                        {
                        }
                    }
                    CODE_SAMPLE
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
    public function refactor(Node $node)
    {
        if (!$this->isObjectType($node, new ObjectType('Shopware\Storefront\Framework\Cache\ReverseProxy\AbstractReverseProxyGateway'))) {
            return null;
        }

        $nodeFinder = new NodeFinder();
        // phpstan-ignore-next-line they should add template support
        $methodNames = \array_map(static fn (ClassMethod $method) => (string) $method->name, $nodeFinder->findInstanceOf([$node], ClassMethod::class));

        if (\in_array('banAll', $methodNames, true)) {
            return null;
        }

        $builderFactory = new BuilderFactory();
        $node->stmts[] = $builderFactory
            ->method('banAll')
            ->makePublic()
            ->addStmt(new Node\Expr\MethodCall(new Node\Expr\Variable('this'), 'ban', [new Node\Arg(new Array_([]))]))
            ->getNode()
        ;

        return $node;
    }
}
