<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v65;

use PhpParser\Node;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class FakerPropertyToMethodCallRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move faker property to method call', [
            new CodeSample(
                <<<'PHP'
$this->faker->randomDigit
PHP,
                <<<'PHP'
$this->faker->randomDigit()
PHP
            ),
        ]);
    }

    public function getNodeTypes(): array
    {
        return [
            Node\Expr\PropertyFetch::class,
        ];
    }

    /**
     * @param Node\Expr\PropertyFetch $node
     */
    public function refactor(Node $node): ?Node\Expr\MethodCall
    {
        if (!$this->isObjectType($node->var, new ObjectType('Faker\Generator'))) {
            return null;
        }

        return new Node\Expr\MethodCall($node->var, $node->name);
    }
}
