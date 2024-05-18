<?php

declare(strict_types=1);

namespace Frosh\Rector\Rule\v65;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
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
            PropertyFetch::class,
        ];
    }

    /**
     * @param PropertyFetch $node
     */
    public function refactor(Node $node): ?MethodCall
    {
        if (!$this->isObjectType($node->var, new ObjectType('Faker\Generator'))) {
            return null;
        }

        return new MethodCall($node->var, $node->name);
    }
}
