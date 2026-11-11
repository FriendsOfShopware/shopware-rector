<?php declare(strict_types=1);

namespace Frosh\Rector\Rule\ClassMethod;

use PhpParser\Node;
use PHPStan\Type\ObjectType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;

final class ChangeReturnTypeOfClassMethodRector extends AbstractRector implements ConfigurableRectorInterface
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
                continue;
            }

            $classMethod = $node->getMethod($config->method);
            if ($classMethod === null) {
                continue;
            }

            if (
                $classMethod->returnType instanceof Node\Name
                && $this->isName($classMethod->returnType, $config->returnType->toString())
            ) {
                continue;
            }

            $classMethod->returnType = new Node\Name\FullyQualified($config->returnType);
            $hasChanged = true;
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
