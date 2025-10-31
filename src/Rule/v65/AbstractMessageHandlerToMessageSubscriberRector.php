<?php declare(strict_types=1);

namespace Frosh\Rector\Rule\v65;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class AbstractMessageHandlerToMessageSubscriberRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces AbstractMessageHandler with MessageSubscriberInterface',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
                        class MyMessageHandler extends AbstractMessageHandler
                        {
                            public static function getHandledMessages(): iterable
                            {
                                return [MyMessage::class];
                            }

                            public function handle(MyMessage $message): void
                            {
                                // do something
                            }
                        }
                        CODE_SAMPLE,
                    <<<'CODE_SAMPLE'
                        class MyMessageHandler implements MessageSubscriberInterface
                        {
                            public function __invoke(MyMessage $message): void
                            {
                                // do something
                            }
                        }
                        CODE_SAMPLE
                ),
            ],
        );
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->extends === null) {
            return null;
        }

        if (!$this->isName($node->extends, 'Shopware\Core\Framework\MessageQueue\Handler\AbstractMessageHandler')) {
            return null;
        }

        $node->extends = null;
        $node->implements[] = new Node\Name\FullyQualified('Symfony\Component\Messenger\Handler\MessageSubscriberInterface');

        $node->attrGroups[] = new Node\AttributeGroup([
            new Node\Attribute(new Node\Name\FullyQualified('Symfony\Component\Messenger\Attribute\AsMessageHandler')),
        ]);

        $handleMethod = $node->getMethod('handle');
        if ($handleMethod) {
            $handleMethod->name = new Node\Identifier('__invoke');
        }

        $getHandledMessagesMethod = $node->getMethod('getHandledMessages');
        if ($getHandledMessagesMethod) {
            foreach ($node->stmts as $key => $stmt) {
                if ($stmt === $getHandledMessagesMethod) {
                    unset($node->stmts[$key]);
                    break;
                }
            }
        }

        return $node;
    }
}
