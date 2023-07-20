<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v65;

use PhpParser\Node;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
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
                    PHP
                ,
                <<<'PHP'
                    $thumbnail->generate(new MediaCollection([$media]), $context);
                    PHP
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [
            Node\Expr\MethodCall::class,
        ];
    }

    /**
     * @param Node\Expr\MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isObjectType($node->var, new ObjectType('Shopware\Core\Content\Media\Thumbnail\ThumbnailService'))) {
            return null;
        }

        if (!$this->isName($node->name, 'generateThumbnails')) {
            return null;
        }

        $node->name = new Node\Name('generate');
        $node->args[0] = new Node\Expr\New_(new Node\Name('Shopware\Core\Content\Media\MediaCollection'), [
            new Node\Expr\Array_([$node->args[0]]),
        ]);

        return $node;
    }
}
