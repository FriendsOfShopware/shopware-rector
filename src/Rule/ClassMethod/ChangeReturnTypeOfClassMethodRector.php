<?php declare(strict_types=1);

namespace Frosh\Rector\Rule\ClassMethod;

use PhpParser\Node;
use PHPStan\Type\ObjectType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;

class ChangeReturnTypeOfClassMethodRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var ChangeReturnTypeOfClassMethod[]
     */
    private array $configuration = [];

    public function getNodeTypes(): array
    {
        return [
            Node\Stmt\Class_::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return null;
        }

        $hasChanged = false;

        foreach ($this->configuration as $config) {
            if (!$this->isObjectType($node, new ObjectType($config->class))) {
                return null;
            }

            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Node\Stmt\ClassMethod && $stmt->name->toString() === $config->method) {
                    $stmt->returnType = new Node\Name\FullyQualified($config->returnType);
                    $hasChanged = true;
                }
            }
        }

        return $hasChanged ? $node : null;
    }

    /**
     * @param ChangeReturnTypeOfClassMethod[] $configuration
     */
    public function configure(array $configuration): void
    {
        $this->configuration = $configuration;
    }
}
