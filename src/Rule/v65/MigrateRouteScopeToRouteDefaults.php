<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v65;

use PhpParser\Builder\Class_;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class MigrateRouteScopeToRouteDefaults extends AbstractRector
{
    protected PhpDocTagRemover $phpDocTagRemover;

    public function __construct(PhpDocTagRemover $phpDocTagRemover)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('NAME', [
            new CodeSample(
                <<<'CODE_SAMPLE'
                    /**
                     * @RouteScope(scopes={"storefront"})
                     */
                    class Controller
                    {
                    }
                    CODE_SAMPLE
                ,
                <<<'PHP'
                    /**
                     * @Route(defaults={"_routeScope"={"storefront"}})
                     */
                    class Controller
                    {
                    }
                    PHP
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [
            Node\Stmt\ClassMethod::class,
            Node\Stmt\Class_::class,
        ];
    }

    /**
     * @param Node\Stmt\ClassMethod|Class_ $node
     */
    public function refactor(Node $node)
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        $routeScope = $phpDocInfo->getByName('RouteScope');

        if ($routeScope === null) {
            return null;
        }

        /** @var SpacelessPhpDocTagNode|null $route */
        $route = $phpDocInfo->getByName('Route');

        if ($route === null) {
            $route = new SpacelessPhpDocTagNode('@Route', new DoctrineAnnotationTagValueNode(new IdentifierTypeNode('@Route')));
            $phpDocInfo->addPhpDocTagNode($route);
        }

        if (!$route->value->getValue('defaults')) {
            $route->value->values[] = new ArrayItemNode(new CurlyListNode([]), 'defaults');
        }

        /** @var CurlyListNode $list */
        $list = $route->value->getValue('defaults')->value;

        /** @var ArrayItemNode $item */
        foreach ($list->values as $item) {
            if ($item->key === '_routeScope') {
                return null;
            }
        }

        $list->values[] = new ArrayItemNode($routeScope->value->values[0]->value, '_routeScope', null, Node\Scalar\String_::KIND_DOUBLE_QUOTED);
        $list->markAsChanged();

        $this->phpDocTagRemover->removeByName($phpDocInfo, 'RouteScope');

        return $node;
    }
}
