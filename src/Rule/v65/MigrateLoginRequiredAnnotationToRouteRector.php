<?php

namespace Frosh\Rector\Rule\v65;

use PhpParser\Builder\Class_;
use PhpParser\Node;
use Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use function var_dump;

class MigrateLoginRequiredAnnotationToRouteRector extends AbstractRector
{
    protected PhpDocTagRemover $phpDocTagRemover;

    public function __construct(PhpDocTagRemover $phpDocTagRemover)
    {
        $this->phpDocTagRemover = $phpDocTagRemover;
    }


    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrates Annotations to Route annotation', [
            new CodeSample(
                <<<'CODE_SAMPLE'
@LoginRequired
@Route("/store-api/product", name="store-api.product.search", methods={"GET", "POST"})
public function myAction()
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
@Route("/store-api/product", name="store-api.product.search", methods={"GET", "POST"}, defaults={"_loginRequired"=true})
public function myAction()
CODE_SAMPLE
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
     * @return null
     */
    public function refactor(Node $node)
    {
        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);

        $loginRequired = $phpDocInfo->getByName('LoginRequired');

        if ($loginRequired === null) {
            return null;
        }

        /** @var SpacelessPhpDocTagNode|null $route */
        $route = $phpDocInfo->getByName('Route');

        if ($route === null) {
            return null;
        }

        if (!$route->value->getValue('defaults')) {
            $route->value->changeValue('defaults', new CurlyListNode());
        }

        /** @var CurlyListNode $list */
        $list = $route->value->getValue('defaults');
        $list->changeValue('_loginRequired', true);

        $this->phpDocTagRemover->removeByName($phpDocInfo, 'LoginRequired');

        return $node;
    }
}