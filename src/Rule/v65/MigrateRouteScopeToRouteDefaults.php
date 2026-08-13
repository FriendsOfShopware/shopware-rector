<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v65;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
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
    public function __construct(private readonly PhpDocTagRemover $phpDocTagRemover, private readonly PhpDocInfoFactory $phpDocFactory, private readonly DocBlockUpdater $docBlockUpdater) {}

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
                    PHP,
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [
            ClassMethod::class,
            Class_::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof ClassMethod && !$node instanceof Class_) {
            return null;
        }

        $phpDocInfo = $this->phpDocFactory->createFromNodeOrEmpty($node);

        $routeScope = $phpDocInfo->getByName('RouteScope');
        if (!$routeScope instanceof PhpDocTagNode) {
            return null;
        }

        $routeScopeValue = $routeScope->value;
        if (!$routeScopeValue instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }

        $route = $phpDocInfo->getByName('Route');
        if (!$route instanceof PhpDocTagNode) {
            $route = new SpacelessPhpDocTagNode('@Route', new DoctrineAnnotationTagValueNode(new IdentifierTypeNode('@Route')));
            $phpDocInfo->addPhpDocTagNode($route);
        }

        $routeValue = $route->value;
        if (!$routeValue instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }

        if ($routeValue->getValue('defaults') === null) {
            $routeValue->values[] = new ArrayItemNode(new CurlyListNode([]), 'defaults');
        }

        $defaults = $routeValue->getValue('defaults');
        if (!$defaults instanceof ArrayItemNode || !$defaults->value instanceof CurlyListNode) {
            return null;
        }

        $list = $defaults->value;

        foreach ($list->values as $item) {
            if ($item instanceof ArrayItemNode && $item->key === '_routeScope') {
                return null;
            }
        }

        $scopeItem = $routeScopeValue->values[0] ?? null;
        if (!$scopeItem instanceof ArrayItemNode) {
            return null;
        }

        $list->values[] = new ArrayItemNode($scopeItem->value, new StringNode('_routeScope'));
        $list->markAsChanged();

        $this->phpDocTagRemover->removeByName($phpDocInfo, 'RouteScope');

        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);

        return $node;
    }
}
