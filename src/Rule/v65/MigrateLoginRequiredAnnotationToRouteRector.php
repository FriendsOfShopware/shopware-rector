<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v65;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class MigrateLoginRequiredAnnotationToRouteRector extends AbstractRector
{
    public function __construct(private readonly PhpDocTagRemover $phpDocTagRemover, private readonly PhpDocInfoFactory $phpDocFactory, private readonly DocBlockUpdater $docBlockUpdater) {}

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrates Annotations to Route annotation', [
            new CodeSample(
                <<<'CODE_SAMPLE'
                    @LoginRequired
                    @Route("/store-api/product", name="store-api.product.search", methods={"GET", "POST"})
                    public function myAction()
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    @Route("/store-api/product", name="store-api.product.search", methods={"GET", "POST"}, defaults={"_loginRequired"=true})
                    public function myAction()
                    CODE_SAMPLE,
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

        $loginRequired = $phpDocInfo->getByName('LoginRequired');

        if ($loginRequired === null) {
            return null;
        }

        $route = $phpDocInfo->getByName('Route');
        if (!$route instanceof PhpDocTagNode) {
            return null;
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
            if ($item instanceof ArrayItemNode && $item->key === '_loginRequired') {
                return null;
            }
        }

        $list->values[] = new ArrayItemNode('true', new StringNode('_loginRequired'));
        $list->markAsChanged();

        $this->phpDocTagRemover->removeByName($phpDocInfo, 'LoginRequired');

        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);

        return $node;
    }
}
