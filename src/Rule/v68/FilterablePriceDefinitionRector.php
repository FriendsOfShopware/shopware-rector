<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v68;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class FilterablePriceDefinitionRector extends AbstractRector
{
    private const FILTERABLE = 'Shopware\Core\Checkout\Cart\Price\Struct\FilterableInterface';

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Declare the explicit interface on filterable price definitions', [new CodeSample(
            'class Price implements PriceDefinitionInterface { public function getFilter(): ?Rule { return null; } }',
            'class Price implements PriceDefinitionInterface, FilterableInterface { public function getFilter(): ?Rule { return null; } }',
        )]);
    }

    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /** @param Class_ $node */
    public function refactor(Node $node): ?Node
    {
        foreach ($node->implements as $interface) {
            if ($this->isName($interface, self::FILTERABLE)) {
                return null;
            }
        }
        if (!$this->isObjectType($node, new ObjectType('Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface')) || $this->isObjectType($node, new ObjectType(self::FILTERABLE))) {
            return null;
        }
        $method = $node->getMethod('getFilter');
        if ($method === null || !$method->isPublic() || $method->isStatic() || $method->params !== [] || $method->returnType === null) {
            return null;
        }
        $type = $method->returnType instanceof Node\NullableType ? $method->returnType->type : $method->returnType;
        if (!$this->isName($type, 'Shopware\Core\Framework\Rule\Rule')) {
            return null;
        }
        $node->implements[] = new FullyQualified(self::FILTERABLE);

        return $node;
    }
}
