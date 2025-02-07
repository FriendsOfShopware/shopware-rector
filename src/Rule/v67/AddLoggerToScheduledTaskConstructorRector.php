<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v67;

use PhpParser\Node;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class AddLoggerToScheduledTaskConstructorRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Ensure that the parent constructor is called with a Psr\Log\LoggerInterface as second argument.', [
            new CodeSample(
                <<<'PHP'
                    class MyTaskHandler extends ScheduledTaskHandler {
                        public function __construct(EntityRepository $scheduledTaskRepository) {
                            parent::__construct($scheduledTaskRepository);
                        }
                    }
                    PHP,
                <<<'PHP'
                    class MyTaskHandler extends ScheduledTaskHandler {
                        public function __construct(EntityRepository $scheduledTaskRepository, LoggerInterface $exceptionLogger) {
                            parent::__construct($scheduledTaskRepository, $exceptionLogger);
                        }
                    }
                    PHP
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [Node\Stmt\Class_::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$this->isObjectType($node, new ObjectType('Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler'))) {
            return null;
        }

        $constructor = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructor instanceof Node\Stmt\ClassMethod) {
            return null;
        }

        $missingScheduledTaskLogger = false;
        foreach ($constructor->stmts as $stmt) {
            if ($stmt instanceof Node\Stmt\Expression && $stmt->expr instanceof Node\Expr\StaticCall) {
                $staticCall = $stmt->expr;
                if ($this->isName($staticCall->name, MethodName::CONSTRUCT) && \count($staticCall->args) === 1) {
                    $missingScheduledTaskLogger = true;
                    $staticCall->args[] = $this->nodeFactory->createArg(new Node\Expr\Variable('exceptionLogger'));

                    break;
                }
            }
        }

        if ($missingScheduledTaskLogger) {
            $constructor->params[] = $this->nodeFactory->createParamFromNameAndType('exceptionLogger', new ObjectType('Psr\Log\LoggerInterface'));
        }

        return null;
    }
}
