<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v65;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class ContextMetadataExtensionToStateRector extends AbstractRector
{
    private const ALLOWED_CONSTS = ['USE_INDEXING_QUEUE', 'DISABLE_INDEXING'];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Migrate extension metadata to state rector',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
                        $context->addExtension(EntityIndexerRegistry::USE_INDEXING_QUEUE, new ArrayEntity());
                        CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
                        $context->addState(EntityIndexerRegistry::USE_INDEXING_QUEUE);
                        CODE_SAMPLE
                ),
            ],
        );
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
        if (!$this->isObjectType($node->var, new ObjectType('Shopware\Core\Framework\Context'))) {
            return null;
        }

        if ((string) $node->name !== 'addExtension') {
            return null;
        }

        /** @var Node|Node\Expr\ClassConstFetch $arg1 */
        $arg1 = $node->args[0]->value;

        if (!$arg1  instanceof Node\Expr\ClassConstFetch || !\in_array($arg1->name->toString(), self::ALLOWED_CONSTS, true)) {
            return null;
        }

        return new MethodCall($node->var, 'addState', [$node->args[0]]);
    }
}
