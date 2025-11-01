<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v65;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Shopware\Core\Content\Media\MediaCollection;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class ThumbnailGenerateSingleToMultiGenerateRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move single thumbnail generation call to batch', [
            new CodeSample(
                <<<'PHP'
                    $thumbnail->generateThumbnails($media, $context);
                    PHP,
                <<<'PHP'
                    $thumbnail->generate(new MediaCollection([$media]), $context);
                    PHP
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [
            MethodCall::class,
        ];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isObjectType($node->var, new ObjectType('Shopware\Core\Content\Media\Thumbnail\ThumbnailService'))) {
            return null;
        }

        if (!$this->isName($node->name, 'generateThumbnails')) {
            return null;
        }

        $node->name = new Name('generate');
        $node->args[0] = new Node\Arg(new New_(new Name\FullyQualified(MediaCollection::class), [
            new Node\Arg(new Array_([new Node\Expr\ArrayItem($node->args[0]->value)])),
        ]));

        return $node;
    }
}
