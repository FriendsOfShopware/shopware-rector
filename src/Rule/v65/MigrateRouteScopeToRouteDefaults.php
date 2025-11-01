<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v65;

use PhpParser\Builder\Class_;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class MigrateRouteScopeToRouteDefaults extends AbstractRector
{
    public function __construct(private readonly PhpDocTagRemover $phpDocTagRemover, private readonly PhpDocInfoFactory $phpDocFactory, private readonly DocBlockUpdater $docBlockUpdater)
    {
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
                    CODE_SAMPLE,
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
            ClassMethod::class,
            Node\Stmt\Class_::class,
        ];
    }

    /**
     * @param ClassMethod|Class_ $node
     */
    public function refactor(Node $node)
    {
        $phpDocInfo = $this->phpDocFactory->createFromNodeOrEmpty($node);

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

        $list->values[] = new ArrayItemNode($routeScope->value->values[0]->value, new StringNode('_routeScope'));
        $list->markAsChanged();

        $this->phpDocTagRemover->removeByName($phpDocInfo, 'RouteScope');

        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);

        return $node;
    }
}
